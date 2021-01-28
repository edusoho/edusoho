<?php

namespace OAuth2\ServerBundle\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

interface AdvancedOAuth2UserInterface extends AdvancedUserInterface
{
    /**
     * Returns the scope granted to the user,
     * space-separated.
     *
     * <code>
     * public function getScope()
     * {
     *     return 'basic email';
     * }
     * </code>
     *
     *
     * @return The user scope
     */
    public function getScope();
}
