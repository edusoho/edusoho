<?php

namespace ApiBundle\Security\Firewall;

use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidCredentialException;
use Symfony\Component\HttpFoundation\Request;

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
            throw new InvalidCredentialException('用户帐号不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw new InvalidCredentialException('帐号密码不正确');
        }

        if ($user['locked']) {
            throw new BannedCredentialException('用户已锁定，请联系网校管理员');
        }

        return $user;
    }
}