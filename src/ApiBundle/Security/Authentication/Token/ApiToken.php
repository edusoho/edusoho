<?php

namespace ApiBundle\Security\Authentication\Token;

use Biz\User\CurrentUser;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiToken extends AbstractToken
{
    private $credentials;
    private $providerKey;
    private $authClass;

    public function __construct(CurrentUser $user, array $roles, $authClass)
    {
        parent::__construct($roles);
        $this->setUser($user);
        parent::setAuthenticated($user->isLogin());
        $this->authClass = $authClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getAuthClass()
    {
        return $this->authClass;
    }

    /**
     * Returns the provider key.
     *
     * @return string The provider key
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        parent::eraseCredentials();

        $this->credentials = null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->credentials, $this->providerKey, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->credentials, $this->providerKey, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
