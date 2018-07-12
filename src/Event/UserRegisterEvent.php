<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserRegisterEvent extends Event
{
	const NAME = 'user.register';

	/**
	 * @var User $userRegistered
	 */
	private $userRegistered;

	public function __construct(User $userRegistered)
	{
		$this->userRegistered = $userRegistered;
	}

	/**
	 * @return User $userRegistered
	 */
	public function getUserRegistered(): User
	{
		return $this->userRegistered;
	}
}
