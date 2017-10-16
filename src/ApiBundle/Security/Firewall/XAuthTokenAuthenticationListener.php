<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class XAuthTokenAuthenticationListener extends BaseAuthenticationListener
{
    const TOKEN_HEADER = 'X-Auth-Token';

    const OLD_TOKEN_HEADER = 'token';

    public function handle(Request $request)
    {
        $token = $this->getTokenStorage()->getToken();
        if (null !== $token && !$token instanceof AnonymousToken) {
            return;
        }

        if (null === $request->headers->get(self::TOKEN_HEADER)
            && null === $request->headers->get(self::OLD_TOKEN_HEADER)) {
            return;
        }

        $tokenInHeader = $request->headers->get(self::TOKEN_HEADER) ? : $request->headers->get(self::OLD_TOKEN_HEADER);

        if (null === $rawToken = $this->getUserService()->getToken('mobile_login', $tokenInHeader)) {
            throw new UnauthorizedHttpException('X-Auth-Token', 'Token is not exist or token is expired', null, ErrorCode::EXPIRED_CREDENTIAL);
        }

        $token = $this->createTokenFromRequest($request, $rawToken['userId']);

        $this->getTokenStorage()->setToken($token);
    }
}
