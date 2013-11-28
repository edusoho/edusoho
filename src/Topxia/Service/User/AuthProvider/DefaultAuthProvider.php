<?php
namespace Topxia\Service\User\AuthProvider;

<<<<<<< HEAD:src/Topxia/Service/User/AuthProvider/NoneAuthProvider.php
class NoneAuthProvider implements AuthProvider
=======
class DefaultAuthProvider implements AuthProvider
>>>>>>> 0b35d49047d53b29c4fb6564341171641737b321:src/Topxia/Service/User/AuthProvider/DefaultAuthProvider.php
{

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

    public function checkPassword($userId, $password)
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
        throw new \RuntimeException();
    }

    public function getProviderName()
    {
        return 'default';
    }
}