<?php
namespace Topxia\Service\User;

interface AuthService
{
    /**
     * 用户注册
     * @param  [array] $registration  注册数据
     * @param  string  $type          注册方式
     * @param  boolean $registerLimit 是否忽略限制注册次数的校验, 默认不忽略,当在后台添加用户,或者导入用户时,启用该字段
     * @return [type]  [description] 返回注册用户.
     */
    public function register($registration, $type = 'default', $registerLimit = false);

    public function syncLogin($userId);

    public function syncLogout($userId);

    public function changeNickname($userId, $newName);

    public function changeEmail($userId, $password, $newEmail);

    public function changePassword($userId, $oldPassword, $newPassword);

    public function changePayPassword($userId, $userLoginPassword, $newPayPassword);

    public function checkUsername($username, $randomName = '');

    public function checkEmailOrMobile($emailOrMobile);

    public function checkEmail($email);

    public function checkMobile($mobile);

    public function checkPassword($userId, $password);

    public function checkPartnerLoginById($userId, $password);

    public function checkPartnerLoginByNickname($nickname, $password);

    public function checkPartnerLoginByEmail($email, $password);

    public function getPartnerAvatar($userId, $size = 'middle');

    public function hasPartnerAuth();

    public function getPartnerName();

    public function isRegisterEnabled();
}
