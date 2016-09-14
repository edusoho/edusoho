<?php
/**
 * Admin role voter;
 * User: retamia
 * Date: 16/9/14
 * Time: 15:20
 */

namespace Permission\PermissionBundle\Security;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdminVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {

    }

    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        return $token->getUser()->isAdmin();
    }
}