<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BasicAuthenticationListener extends BaseAuthenticationListener
{
    public function handle(Request $request)
    {
        if (null !== $this->getTokenStorage()->getToken()) {
            return;
        }

        if (null === $username = $request->headers->get('PHP_AUTH_USER')) {
            return;
        }

        $user = $this->validUser($username, $request->headers->get('PHP_AUTH_PW'));
        $token = $this->createTokenFromRequest($request, $user['id']);
        $this->getTokenStorage()->setToken($token);
    }

    private function validUser($username, $password)
    {
        $user = $this->getUserService()->getUserByLoginField($username);
        if (empty($user)) {
            throw new UnauthorizedHttpException('Basic', '用户帐号不存在', null, ErrorCode::INVALID_CREDENTIAL);
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw new UnauthorizedHttpException('Basic', '帐号密码不正确', null, ErrorCode::INVALID_CREDENTIAL);
        }

        if ($user['locked']) {
            throw new UnauthorizedHttpException('Basic', '用户已锁定，请联系网校管理员', null, ErrorCode::BANNED_CREDENTIAL);
        }

        return $user;
    }
}
