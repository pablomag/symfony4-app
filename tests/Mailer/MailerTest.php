<?php

namespace App\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Mailer\Mailer;

class MailerTest extends TestCase
{
	private const FROMMAIL = 'webmaster@app.com';
	private const USERMAIL = 'test@email.com';

	public function testConfirmationEmail()
	{
		$user = new User();
		$user->setEmail(self::USERMAIL);

		$swiftMock = $this->getMockBuilder(\Swift_Mailer::class)
							->disableOriginalConstructor()
							->getMock();

		$twigMock = $this->getMockBuilder(\Twig_Environment::class)
							->disableOriginalConstructor()
							->getMock();

		$swiftMock->expects($this->once())->method('send')
					->with($this->callback(function($subject)
					{
						$message = (string)$subject;

						return strpos($message, 'From: '.self::FROMMAIL) !== false
							&& strpos($message, 'Content-Type: text/html') !== false
							&& strpos($message, 'Subject: Thank you for registering in App!') !== false
							&& strpos($message, 'To: '.self::USERMAIL) !== false
							&& strpos($message, 'Message body test') !== false;
					}));

		$twigMock->expects($this->once())->method('render')
					->with('register/confirmation.html.twig', ['user' => $user])
					->willReturn('Message body test');

		$mailer = new Mailer($swiftMock, $twigMock, self::FROMMAIL);
		$mailer->sendConfirmationEmail($user);
	}
}
