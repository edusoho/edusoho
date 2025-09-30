<?php

namespace Biz\User\Service;

interface AuthService
{
    public function register($registration, $type = 'default');

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

    public function hasPartnerAuth();

    public function isRegisterEnabled();
}
