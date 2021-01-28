<?php

namespace Biz\User\AuthProvider;

use AppBundle\Common\Exception\UnexpectedValueException;

class DefaultAuthProvider implements AuthProvider
{
    public function checkConnect()
    {
        return false;
    }

    public function register($registration)
    {
        return $registration;
    }

    public function syncLogin($userId)
    {
        return true;
    }

    public function syncLogout($userId)
    {
        return true;
    }

    public function changeNickname($userId, $newName)
    {
        return true;
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        return true;
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        return true;
    }

    public function checkUsername($username)
    {
        return array('success', '');
    }

    public function checkEmail($email)
    {
        return array('success', '');
    }

    public function checkMobile($mobile)
    {
        return array('success', '');
    }

    public function checkPassword($userId, $password)
    {
        return false;
    }

    public function checkLoginById($userId, $password)
    {
        return false;
    }

    public function checkLoginByNickname($nickname, $password)
    {
        return false;
    }

    public function checkLoginByEmail($email, $password)
    {
        return false;
    }

    public function getAvatar($userId, $size = 'middle')
    {
        throw new UnexpectedValueException('');
    }

    public function getProviderName()
    {
        return 'default';
    }
}
