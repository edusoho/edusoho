<?php

namespace ApiBundle\Security\Firewall;

use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class OldTokenAuthenticationListener extends BaseAuthenticationListener
{
    const OLD_TOKEN_HEADER = 'token';

    public function handle(Request $request)
    {
        $token = $this->getTokenStorage()->getToken();
        if (null !== $token && !$token instanceof AnonymousToken) {
            return;
        }

        $tokenInHeader = $request->headers->get(self::OLD_TOKEN_HEADER);
        if (!$tokenInHeader || strtolower($tokenInHeader) == 'null') {
            return;
        }

        if (null === $rawToken = $this->getUserService()->getToken('mobile_login', $tokenInHeader)) {
            throw UserException::NOTFOUND_TOKEN();
        }

        $token = $this->createTokenFromRequest($request, $rawToken['userId'], $rawToken['token']);

        $this->getTokenStorage()->setToken($token);
    }
}
