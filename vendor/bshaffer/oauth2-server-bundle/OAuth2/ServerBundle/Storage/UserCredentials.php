<?php

namespace OAuth2\ServerBundle\Storage;

use OAuth2\Storage\UserCredentialsInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use OAuth2\ServerBundle\User\OAuth2UserInterface;
use OAuth2\ServerBundle\User\AdvancedOAuth2UserInterface;

class UserCredentials implements UserCredentialsInterface
{
    private $em;
    private $up;
    private $encoderFactory;

    public function __construct(EntityManager $entityManager, UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory)
    {
        $this->em = $entityManager;
        $this->up = $userProvider;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials($username, $password)
    {
        // Load user by username
        try {
            $user = $this->up->loadUserByUsername($username);
        } catch (\Symfony\Component\Security\Core\Exception\UsernameNotFoundException $e) {
            return false;
        }

        // Do extra checks if implementing the AdvancedUserInterface
        if ($user instanceof AdvancedUserInterface) {
            if ($user->isAccountNonExpired() === false) return false;
            if ($user->isAccountNonLocked() === false) return false;
            if ($user->isCredentialsNonExpired() === false) return false;
            if ($user->isEnabled() === false) return false;
        }

        // Check password
        if ($this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            return true;
        }

        return false;
    }

    /**
     * @return
     * ARRAY the associated "user_id" and optional "scope" values
     * This function MUST return FALSE if the requested user does not exist or is
     * invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     * return array(
     *     "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *     "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     * );
     * @endcode
     */
    public function getUserDetails($username)
    {
        // Load user by username
        try {
            $user = $this->up->loadUserByUsername($username);
        } catch (\Symfony\Component\Security\Core\Exception\UsernameNotFoundException $e) {
            return false;
        }

        // If user implements OAuth2UserInterface or AdvancedOAuth2UserInterface
        // then we can get the scopes, score!
        if ($user instanceof OAuth2UserInterface || $user instanceof AdvancedOAuth2UserInterface) {
            $scope = $user->getScope();
        } else {
            $scope = null;
        }

        return array(
            'user_id' => $user->getUsername(),
            'scope' => $scope
        );
    }
}
