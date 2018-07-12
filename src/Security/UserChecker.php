<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Security\Core\Exception\AccountExpiredException;

class UserChecker implements UserCheckerInterface
{
	public function checkPreAuth(UserInterface $user)
	{
		if (!$user instanceof User) {
			return;
		}

		if (!$user->isActive())
		{
			throw new DisabledException('Account disabled. Please confirm your account to activate it.');
		}
	}

	public function checkPostAuth(UserInterface $user)
	{
		if (!$user instanceof User) {
			return;
		}

		/*if ($user->isExpired())
		{
			throw new AccountExpiredException('Not used');
		}*/
	}
}
