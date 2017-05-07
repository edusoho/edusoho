<?php

namespace AppBundle\Component\OAuthServer\Storage;

use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use OAuth2\Storage\UserCredentialsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserCredentials implements UserCredentialsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');
    }

    public function checkUserCredentials($username, $password)
    {
        // Load user by username
        try {
            $user = $this->getUserService()->getUserByLoginField($username);
        } catch (\Symfony\Component\Security\Core\Exception\UsernameNotFoundException $e) {
            return false;
        }

        return $this->getUserService()->verifyPassword($user['id'], $password);
    }

    public function getUserDetails($username)
    {
        // Load user by username
        try {
            $user = $this->getUserService()->getUserByLoginField($username);
        } catch (\Symfony\Component\Security\Core\Exception\UsernameNotFoundException $e) {
            return false;
        }

        return array(
            'user_id' => $user['id'],
            'scope' => 'default',
        );
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
