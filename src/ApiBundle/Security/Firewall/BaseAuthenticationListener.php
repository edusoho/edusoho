<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Security\Authentication\Token\ApiToken;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Biz\User\CurrentUser;

abstract class BaseAuthenticationListener implements ListenerInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function checkUserLocked($userId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            return;
        }

        if ($user['locked']) {
            throw UserException::LOCKED_USER();
        }
    }

    protected function createTokenFromRequest(Request $request, $userId, $loginToken = '')
    {
        $user = $this->getUserService()->getUser($userId);
        $currentUser = new CurrentUser();
        $user['currentIp'] = $request->getClientIp();
        $user['loginToken'] = $loginToken;
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return new ApiToken($currentUser, $currentUser->getRoles());
    }

    protected function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->container->get('biz')->service('User:UserService');
    }
}
