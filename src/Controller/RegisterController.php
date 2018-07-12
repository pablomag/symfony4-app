<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserRegisterEvent;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Security\TokenGenerator;

class RegisterController extends Controller
{
	/**
	 * @Route("/register", name="user_register")
	 */
	public function register
	(
		UserPasswordEncoderInterface $passwordEncoder,
		Request $request,
		EventDispatcherInterface $eventDispatcher,
		TokenGenerator $tokenGenerator
	){
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$password = $passwordEncoder->encodePassword(
				$user,
				$user->getPlainPassword()
			);

			$user->setPassword($password);
			$user->setConfirmationToken($tokenGenerator->getRandomSecureToken(30));

			if ($user->getUsername() === 'Admin')
			{
				$user->setRoles(['ROLE_ADMIN']);
			} else {

				$user->setRoles(['ROLE_USER']);
			}

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($user);
			$entityManager->flush();

			$userRegisterEvent = new UserRegisterEvent($user);

			$eventDispatcher->dispatch(
				UserRegisterEvent::NAME,
				$userRegisterEvent
			);

			return $this->redirectToRoute('security_login');
		}

		return $this->render('register/register.html.twig',
		[
			'form' => $form->createView()
		]);
	}
}
