<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidCredentialException;
use ApiBundle\Security\Authentication\Token\ApiToken;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BasicAuthenticationListener implements ListenerInterface
{
    private $tokenStorage;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(TokenStorageInterface $tokenStorage, Biz $biz)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userService = $biz->service('User:UserService');
    }

    public function handle(Request $request)
    {
        if (null !== $this->tokenStorage->getToken()) {
            return;
        }

        if (null === $username = $request->headers->get('PHP_AUTH_USER')) {
            return;
        }

        $user = $this->isValidUser($username, $request->headers->get('PHP_AUTH_PW'));
        $token = $this->createToken($user, $request->getClientIp());
        $this->tokenStorage->setToken($token);

    }

    private function isValidUser($username, $password)
    {
        $user = $this->userService->getUserByLoginField($username);
        if (empty($user)) {
            throw new InvalidCredentialException('用户帐号不存在');
        }

        if (!$this->userService->verifyPassword($user['id'], $password)) {
            throw new InvalidCredentialException('帐号密码不正确');
        }

        if ($user['locked']) {
            throw new BannedCredentialException('用户已锁定，请联系网校管理员');
        }

        return $user;
    }

    private function createToken($user, $clientIp)
    {
        $currentUser = new CurrentUser();
        $user['currentIp'] = $clientIp;
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return new ApiToken($currentUser, $currentUser->getRoles());
    }

}