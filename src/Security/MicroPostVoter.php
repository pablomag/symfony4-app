<?php

namespace App\Security;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MicroPostVoter extends Voter
{
	const EDIT = 'edit';
	const DELETE = 'delete';
	/**
	 * @var AccessDecisionManagerInterface
	 */
	private $decisionManager;

	public function __construct(
		AccessDecisionManagerInterface $decisionManager
	){
		$this->decisionManager = $decisionManager;
	}

	protected function supports($attribute, $subject)
	{
		return (in_array($attribute, [self::EDIT, self::DELETE])
				&& $subject instanceof MicroPost);
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
	{
		if ($this->decisionManager->decide($token, [User::ROLE_ADMIN]))
			return true;

		$authenticatedUser = $token->getUser();

		if (!$authenticatedUser instanceof User)
			return false;

		$microPost = $subject;

		return $microPost->getUser()->getId() === $authenticatedUser->getId();
	}
}
