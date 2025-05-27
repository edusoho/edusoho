<?php

namespace ApiBundle\Security\Firewall;

use Biz\System\Service\SettingService;
use Biz\User\UserException;
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
            throw UserException::NOTFOUND_USER();
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw UserException::PASSWORD_ERROR();
        }
        $loginBind = $this->getSettingService()->get('login_bind');
        $skipPasswordUpdate = $user['roles'] === ['ROLE_USER'] && !empty($loginBind['login_strong_pwd_enable']) && 0 == $loginBind['login_strong_pwd_enable'];
        if (!$skipPasswordUpdate) {
            if ($this->getUserService()->validatePassword($password)) {
                $this->getUserService()->updateUser($user['id'], ['passwordUpgraded' => 1]);
            } else {
                throw UserException::PASSWORD_REQUIRE_UPGRADE();
            }
        }

        if ($user['locked']) {
            throw UserException::LOCKED_USER();
        }

        return $user;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }
}
