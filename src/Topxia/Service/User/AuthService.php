<?php
namespace Topxia\Service\User;

interface AuthService
{
    public function register($registration);

    public function syncLogin($userId);

    public function syncLogout($userId);

    public function changeUsername($userId, $newName);

    public function changeEmail($userId, $newEmail);

    public function changePassowrd($userId, $newPassword);

    public function checkUsername($username);

    public function checkEmail($email);

    public function checkPassword($userId, $password);

    public function checkPartnerLoginByEmail($email, $password);

    public function hasPartnerAuth();

    public function getPartnerName();
}