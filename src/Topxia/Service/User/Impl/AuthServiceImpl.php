<?php
namespace Topxia\Service\User\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\User\AuthService;

class AuthServiceImpl extends BaseService implements AuthService
{
    private $provider = null;

    public function register($registration)
    {
        $authUser = $this->getAuthProvider()->register($registration);

        $registration['token'] = array(
            'userId' => $authUser['id'],
        );

        return $this->getUserService()->register($registration, $this->getAuthProvider()->getProviderName());
    }

    public function syncLogin($userId)
    {
        $providerName = $this->getAuthProvider()->getProviderName();
        $bind = $this->getUserService()->getUserBindByTypeAndUserId($providerName, $userId);
        if (empty($bind)) {
            return '';
        }

        return $this->getAuthProvider()->syncLogin($bind['fromId']);
    }

    public function syncLogout($userId)
    {
        $providerName = $this->getAuthProvider()->getProviderName();
        $bind = $this->getUserService()->getUserBindByTypeAndUserId($providerName, $userId);
        if (empty($bind)) {
            return '';
        }

        return $this->getAuthProvider()->syncLogout($bind['fromId']);
    }

    public function changeNickname($userId, $newName)
    {
        if ($this->hasPartnerAuth()) {
            $providerName = $this->getAuthProvider()->getProviderName();
            $bind = $this->getUserService()->getUserBindByTypeAndUserId($providerName, $userId);
            if ($bind) {
                $this->getAuthProvider()->changeNickanme($bind['fromId'], $newName);
            }
        }

        $this->getUserService()->changeNickanme($userId, $newName);
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        if ($this->hasPartnerAuth()) {
            $providerName = $this->getAuthProvider()->getProviderName();
            $bind = $this->getUserService()->getUserBindByTypeAndUserId($providerName, $userId);
            if ($bind) {
                $this->getAuthProvider()->changeEmail($bind['fromId'], $password, $newEmail);
            }
        }
        $this->getUserService()->changeEmail($userId, $newEmail);
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        if ($this->hasPartnerAuth()) {
            $providerName = $this->getAuthProvider()->getProviderName();
            $bind = $this->getUserService()->getUserBindByTypeAndUserId($providerName, $userId);
            if ($bind) {
                $this->getAuthProvider()->changePassword($bind['fromId'], $oldPassword, $newPassword);
            }
        }

        $this->getUserService()->changePassword($userId, $newPassword);
    }

    public function checkUsername($username)
    {
        $result = $this->getAuthProvider()->checkUsername($username);
        if ($result[0] != 'success') {
            return $result;
        }


        $avaliable = $this->getUserService()->isNicknameAvaliable($username);
        if (!$avaliable) {
            return array('error_duplicate', '名称已存在!');
        }

        return array('success', '');
    }

    public function checkEmail($email)
    {
        $result = $this->getAuthProvider()->checkEmail($email);
        if ($result[0] != 'success') {
            return $result;
        }
        $avaliable = $this->getUserService()->isEmailAvaliable($email);
        if (!$avaliable) {
            return array('error_duplicate', 'Email已存在!');
        }

        return array('success', '');
    }

    public function checkPassword($userId, $password)
    {
        $checked = $this->getAuthProvider()->checkPassword($userId, $password);
        if ($checked) {
            return true;
        }
        return $this->getUserService()->checkPassword($userId, $password);
    }

    public function checkPartnerLoginByEmail($email, $password)
    {
        return $this->getAuthProvider()->checkLoginByEmail($email, $password);
    }

    public function hasPartnerAuth()
    {
        return $this->getAuthProvider()->getProviderName() != 'default';
    }

    public function getPartnerName()
    {
        return $this->getAuthProvider()->getProviderName();
    }

    private function getAuthProvider()
    {
        if (!$this->provider) {
            $provider = $this->getKernel()->getParameter('user_partner');
            if (!in_array($provider, array('discuz', 'phpwind', 'none'))) {
                throw new \InvalidArgumentException();
            }

            $class = substr(__NAMESPACE__, 0, -5) . "\\AuthProvider\\" . ucfirst($provider) . "AuthProvider";

            $this->provider = new $class();
        }

        return $this->provider;
    }

    private function getUserService()
    {
        return $this->createService('User.UserService');
    }

}