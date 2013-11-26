<?php
namespace Topxia\Service\User\AuthProvider;

interface AuthProvider
{

    public function register($registration);

    public function syncLogin($userId);

    public function syncLogout();

    public function changeUsername($userId, $newName);

    public function changeEmail($userId, $newEmail);

    public function changePassowrd($userId, $newPassword);

    public function checkUsername($username);

    public function checkEmail($email);

    public function checkPassword($userId, $password);

    public function getProviderName();

}