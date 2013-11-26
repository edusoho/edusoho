<?php
namespace Topxia\Service\User\AuthProvider;

interface AuthProvider
{

    public function register($registration);

    public function syncLogin($userId);

    public function syncLogout();

    public function changeNickname($userId, $newName);

    public function changeEmail($userId, $password, $newEmail);

    public function changePassword($userId, $oldPassword, $newPassword);

    public function checkUsername($username);

    public function checkEmail($email);

    public function checkPassword($userId, $password);

    public function checkLoginByEmail($email, $password);

    public function getProviderName();


}