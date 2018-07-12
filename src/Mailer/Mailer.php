<?php

namespace App\Mailer;

use App\Entity\User;

class Mailer
{
	/**
	 * @var \Swift_Mailer $mailer
	 * @var \Twig_Environment $twig
	 */
	private $mailer;
	private $twig;

	public function __construct
	(
		\Swift_Mailer $mailer,
		\Twig_Environment $twig,
		string $mailFrom
	){
		$this->mailer = $mailer;
		$this->twig = $twig;
		$this->mailFrom = $mailFrom;
	}

	/**
	 * @param User $user
	 */
	public function sendConfirmationEmail(User $user)
	{
		$body = $this->twig->render('register/confirmation.html.twig',
		[
			'user' => $user
		]);

		$message = (new \Swift_Message())
			->setSubject('Thank you for registering in App!')
			->setFrom($this->mailFrom)
			->setTo($user->getEmail())
			->setBody($body, 'text/html');

		$this->mailer->send($message);
	}
}
