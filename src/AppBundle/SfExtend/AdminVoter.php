<?php

namespace AppBundle\SfExtend;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Biz\User\CurrentUser;

class AdminVoter implements VoterInterface
{
    const ADMIN = 'ROLE_ADMIN';
    const BACKEND = 'ROLE_BACKEND';

    public function supportsAttribute($attribute)
    {
        return $attribute === self::ADMIN || $attribute === self::BACKEND;
    }

    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        $user = $token->getUser();

        if (empty($user) || !$user instanceof CurrentUser) {
            return self::ACCESS_DENIED;
        }

        if ($token->getUser()->isAdmin()) {
            return self::ACCESS_GRANTED;
        } else {
            return self::ACCESS_DENIED;
        }
    }
}
