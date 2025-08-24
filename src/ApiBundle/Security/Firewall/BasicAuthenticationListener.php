<?php

namespace ApiBundle\Security\Firewall;

use Biz\System\Service\SettingService;
use Biz\User\Support\PasswordValidator;
use Biz\User\Support\RoleHelper;
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

        $passwordLevel = PasswordValidator::getLevel($password);
        if ($user['passwordUpgraded'] != $passwordLevel) {
            $this->getUserService()->updateUser($user['id'], ['passwordUpgraded' => $passwordLevel]);
            $user['passwordUpgraded'] = $passwordLevel;
        }

        if (RoleHelper::isStudent($user['roles'])) {
            $loginBindSetting = $this->getSettingService()->get('login_bind');
            if (($loginBindSetting['student_weak_password_check'] ?? 0) && !PasswordValidator::isValidLevel($user['passwordUpgraded'])) {
                throw UserException::PASSWORD_REQUIRE_UPGRADE();
            }
        } else {
            if (!PasswordValidator::isStrongLevel($user['passwordUpgraded'])) {
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
