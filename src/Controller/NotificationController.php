<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_USER')")
 * @Route("/notification")
 */
class NotificationController extends Controller
{
	public function __construct(NotificationRepository $notificationRepository)
	{
		$this->notificationRepository = $notificationRepository;
	}

	/**
	 * @Route("/unread-count", name="notification_unread")
	 */
	public function unreadCount()
	{
		return new JsonResponse(
		[
			'count' => $this->notificationRepository->findUnseenByUser($this->getUser())
		]);
	}

	/**
	 * @Route("/unseen", name="notification_unseen")
	 */
	public function notifications()
	{
		return $this->render('notification/notifications.html.twig',
		[
			'notifications' => $this->notificationRepository->findBy(
			[
				'seen' => false,
				'user' => $this->getUser()
			])
		]);
	}

	/**
	 * @Route("/markasread/{id}", name="notification_mark_as_read")
	 */
	public function markAsRead(Notification $notification)
	{
		$notification->setSeen(true);
		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('notification_unseen');
	}

	/**
	 * @Route("/markallasread", name="notification_mark_all_as_read")
	 */
	public function markAllAsRead()
	{
		$this->notificationRepository->markAllAsReadByUser($this->getUser());
		$this->getDoctrine()->getManager()->flush();

		return $this->redirectToRoute('notification_unseen');
	}
}
