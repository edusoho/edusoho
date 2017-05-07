<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\InvalidCredentialException;
use ApiBundle\Security\Authentication\Token\ApiToken;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TokenHeaderListener implements ListenerInterface
{
    private $tokenStorage;
    private $userService;

    const TOKEN_HEADER = 'X-Auth-Token';

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

        if (null === $tokenInHeader = $request->headers->get(self::TOKEN_HEADER)) {
            return;
        }

        if (null === $rawToken = $this->userService->getToken(TokenService::TYPE_API_AUTH, $tokenInHeader)) {
            throw new InvalidCredentialException('Token is not exist or token is expired');
        }

        $token = $this->createToken($rawToken, $request->getClientIp());

        $this->tokenStorage->setToken($token);
    }

    private function createToken($rawToken, $clientIp)
    {
        $currentUser = $this->createCurrentUser($rawToken, $clientIp);

        return new ApiToken($currentUser, $currentUser->getRoles());
    }

    private function createCurrentUser($rawToken, $clientIp)
    {
        $user = $this->userService->getUser($rawToken['userId']);
        $currentUser = new CurrentUser();
        $user['currentIp'] = $clientIp;
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));

        return $currentUser;
    }


}
