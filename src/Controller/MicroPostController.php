<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/micro-post")
 */
class MicroPostController
{
	/**
	 * @var \Twig_Environment
	 * @var MicroPostRepository
	 * @var FormFactoryInterface
	 * @var EntityManager
	 * @var RouterInterface
	 * @var FlashBagInterface
	 * @var AuthorizationCheckerInterface
	 */
	private $twig;
	private $microPostRepository;
	private $formFactory;
	private $entityManager;
	private $router;
	private $flashBag;
	private $authorizationChecker;

	public function __construct(
		\Twig_Environment $twig,
		MicroPostRepository $microPostRepository,
		FormFactoryInterface $formFactory,
		EntityManagerInterface $entityManager,
		RouterInterface $router,
		FlashBagInterface $flashBag,
		AuthorizationCheckerInterface $authorizationChecker
	){
		$this->twig = $twig;
		$this->microPostRepository = $microPostRepository;
		$this->formFactory = $formFactory;
		$this->entityManager = $entityManager;
		$this->router = $router;
		$this->flashBag = $flashBag;
		$this->authorizationChecker = $authorizationChecker;
	}

	/**
	 * @Route("/", name="micro_post_index")
	 */
	public function index(TokenStorageInterface $tokenStorage, UserRepository $userRepository)
	{
		$currentUser = $tokenStorage->getToken()->getUser();

		$userToFollow = [];

		if ($currentUser instanceof User)
		{
			$posts = $this->microPostRepository->findAllByUsers(
				$currentUser->getFollowing(),
				$currentUser
			);

			$userToFollow = count($posts) === 0 ? $userRepository->findAllWithMoreThan5PostsExceptUser($currentUser) : [];
		} else {
			$posts = $this->microPostRepository->findBy([], ['time' => 'DESC']);
		}

		$html = $this->twig->render('micro-post/index.html.twig',
		[
			'posts' => $posts,
			'usersToFollow' => $userToFollow
		]);

		return new Response($html);
	}

	/**
	 * @Route("/add", name="micro_post_add")
	 * @Security("is_granted('ROLE_USER')")
	 */
	public function add(Request $request, TokenStorageInterface $tokenStorage)
	{
		$user = $tokenStorage->getToken()->getUser();

		$microPost = new MicroPost();
		$microPost->setUser($user);

		$form = $this->formFactory->create(MicroPostType::class, $microPost);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$this->entityManager->persist($microPost);
			$this->entityManager->flush();

			return new RedirectResponse($this->router->generate('micro_post_index'));
		}

		$html = $this->twig->render('micro-post/form.html.twig',
		[
			'form' => $form->createView()
		]);

		return new Response($html);
	}

	/**
	 * @Route("/{id}", name="micro_post_show")
	 */
	public function show(MicroPost $microPost)
	{
		$html = $this->twig->render('micro-post/show.html.twig',
		[
			'post' => $microPost
		]);

		return new Response($html);
	}

	/**
	 * @Route("/user/{username}", name="micro_post_user")
	 */
	public function userPosts(User $user)
	{
		$html = $this->twig->render('micro-post/user-posts.html.twig',
		[
			'posts' => $this->microPostRepository->findBy(
				['user' => $user],
				['time' => 'DESC']
			),
			'user' => $user
		]);

		return new Response($html);
	}

	/**
	 * @Route("/edit/{id}", name="micro_post_edit")
	 */
	public function edit(MicroPost $microPost, Request $request)
	{
		if (!$this->authorizationChecker->isGranted('edit', $microPost))
			throw new UnauthorizedHttpException();

		$form = $this->formFactory->create(MicroPostType::class, $microPost);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$this->entityManager->flush();

			return new RedirectResponse($this->router->generate('micro_post_index'));
		}

		$html = $this->twig->render('micro-post/form.html.twig',
		[
			'form' => $form->createView()
		]);

		return new Response($html);
	}

	/**
	 * @Route("/delete/{id}", name="micro_post_delete")
	 * @Security("is_granted('delete', microPost)", message="Access denied")
	 */
	public function delete(MicroPost $microPost)
	{
		$this->entityManager->remove($microPost);
		$this->entityManager->flush();

		$this->flashBag->add('notice', 'Post '.$microPost->getId().' removed');

		return new RedirectResponse($this->router->generate('micro_post_index'));
	}
}
