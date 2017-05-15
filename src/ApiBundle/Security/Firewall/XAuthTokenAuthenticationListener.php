<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
            throw new UnauthorizedHttpException('X-Auth-Token', 'Token is not exist or token is expired', null, ErrorCode::EXPIRED_CREDENTIAL);
        }

        $token = $this->createTokenFromRequest($request, $rawToken['userId']);

        $this->getTokenStorage()->setToken($token);
    }
}
