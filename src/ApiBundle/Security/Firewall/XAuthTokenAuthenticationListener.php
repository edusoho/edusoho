<?php

namespace ApiBundle\Security\Firewall;

use Biz\User\Support\PasswordValidator;
use Biz\User\Support\RoleHelper;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class XAuthTokenAuthenticationListener extends BaseAuthenticationListener
{
    const TOKEN_HEADER = 'X-Auth-Token';

    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        if (null === $tokenInHeader = $request->headers->get(self::TOKEN_HEADER)) {
            return;
        }

        if (null === $rawToken = $this->getUserService()->getToken('mobile_login', $tokenInHeader)) {
            throw UserException::NOTFOUND_TOKEN();
        }
        $user = $this->getUserService()->getUser($rawToken['userId']);
        if (RoleHelper::isStaff($user['roles']) && !PasswordValidator::isStrongLevel($user['passwordUpgraded'])) {
            throw UserException::NOTFOUND_TOKEN();
        }

        $token = $this->createTokenFromRequest($request, $rawToken['userId'], $rawToken['token']);

        $this->getTokenStorage()->setToken($token);
    }
}
