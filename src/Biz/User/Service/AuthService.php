<?php

namespace Biz\User\Service;

interface AuthService
{
    public function register($registration, $type = 'default');

    public function syncLogin($userId);

    public function syncLogout($userId);

    public function changeNickname($userId, $newName);

    public function changeEmail($userId, $password, $newEmail);

    public function changePassword($userId, $oldPassword, $newPassword);

    public function changePayPassword($userId, $userLoginPassword, $newPayPassword);

    public function changePayPasswordWithoutLoginPassword($userId, $newPayPassword);

    public function checkUsername($username, $randomName = '');

    public function checkEmailOrMobile($emailOrMobile);

    public function checkEmail($email);

    public function checkMobile($mobile);

    public function checkPassword($userId, $password);

    public function checkPayPassword($userId, $payPassword);

    public function checkPartnerLoginById($userId, $password);

    public function checkPartnerLoginByNickname($nickname, $password);

    public function checkPartnerLoginByEmail($email, $password);

    public function getPartnerAvatar($userId, $size = 'middle');

    public function hasPartnerAuth();

    public function getPartnerName();

    public function isRegisterEnabled();
}
