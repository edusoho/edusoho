<?php
namespace Topxia\Service\User\AuthProvider;

class UcenterAuthProvider implements AuthProvider
{

    public function register($registration)
    {
        return $registration;
    }

    public function syncLogin($userId)
    {
        return true;
    }

    public function syncLogout()
    {
        return true;
    }

    public function changeNickname($userId, $newName)
    {
        return true;
    }

    public function changeEmail($userId, $newEmail)
    {
        return true;
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        return true;
    }

    public function checkUsername($username)
    {
        return true;
    }

    public function checkEmail($email)
    {
        return true;
    }

    public function checkPassword($userId, $password)
    {
        return false;
    }

    public function checkLoginByEmail($email, $password)
    {
        return false;
    }

    public function getProviderName()
    {
        return 'default';
    }
}