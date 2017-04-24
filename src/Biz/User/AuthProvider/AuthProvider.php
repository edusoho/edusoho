<?php

namespace Biz\User\AuthProvider;

interface AuthProvider
{
    public function checkConnect();

    public function register($registration);

    public function syncLogin($userId);

    public function syncLogout($userId);

    public function changeNickname($userId, $newName);

    public function changeEmail($userId, $password, $newEmail);

    public function changePassword($userId, $oldPassword, $newPassword);

    public function checkUsername($username);

    public function checkEmail($email);

    public function checkMobile($mobile);

    public function checkPassword($userId, $password);

    public function checkLoginById($userId, $password);

    public function checkLoginByNickname($nickname, $password);

    public function checkLoginByEmail($email, $password);

    public function getAvatar($userId, $size = 'middle');

    public function getProviderName();
}
