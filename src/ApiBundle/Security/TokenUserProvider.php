<?php

namespace ApiBundle\Security;

use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class TokenUserProvider implements UserProviderInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadUserByUsername($apiToken)
    {
        if (!empty($apiToken['allowed_without_user'])) {
            $currentUser = $this->setCurrentUser(null);
        } else {
            $user = $this->getUserService()->getUser($apiToken['userId']);

            if (empty($user)) {
                throw new UsernameNotFoundException(sprintf('User not found.'));
            }

            $currentUser = $this->setCurrentUser($user);
        }

        return $currentUser;
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return $class === 'Biz\User\CurrentUser';
    }

    private function setCurrentUser($user)
    {
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = array(
                'id' => 0,
                'nickname' => '游客',
                'email' => ' '
            );
        }

        $user['currentIp'] = $this->getCurrentIp();
        $user['roles'][] = 'ROLE_API';
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $biz = $this->container->get('biz');
        $biz['user'] = $currentUser;

        return $currentUser;
    }

    private function getCurrentIp()
    {
        $request = $this->container->get('request');
        return $request->getClientIp();
    }

    private function getUserService()
    {
        return $this->container->get('biz')->service('User:UserService');
    }
}