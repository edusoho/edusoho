<?php
namespace Topxia\Service\User\AuthProvider;

class DiscuzAuthProvider implements AuthProvider
{
    public function register($registration)
    {
        $this->initDiscuzApi();

        $result = uc_user_register($registration['nickname'], $registration['password'], $registration['email']);

        if ($result < 0) {
            $result = $this->convertApiResult($result);
            throw new \RuntimeException("{$result[0]}:{$result[1]}");
        }

        $registration['id'] = $result;

        return $registration;
    }

    public function syncLogin($userId)
    {
        $this->initDiscuzApi();
        return uc_user_synlogin($userId);
    }

    public function syncLogout($userId)
    {
        $this->initDiscuzApi();
        return uc_user_synlogout();
    }

    public function changeNickname($userId, $newName)
    {
        return true;
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        $this->initDiscuzApi();
        $user = uc_get_user($userId, 1);
        $result = uc_user_edit($user[1], null, null, $newEmail, 1);
        return $result == 1;
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $this->initDiscuzApi();
        $user = uc_get_user($userId, 1);
        $result = uc_user_edit($user[1], null, $newPassword, null, 1);
        return $result == 1;
    }

    public function checkUsername($username)
    {
        $this->initDiscuzApi();
        $result = uc_user_checkname($username);
        return $this->convertApiResult($result);
    }

    public function checkEmail($email)
    {
        $this->initDiscuzApi();
        $result = uc_user_checkemail($email);
        return $this->convertApiResult($result);
    }

    public function checkPassword($userId, $password)
    {
        return false;
    }

    public function checkLoginByEmail($email, $password)
    {
        $this->initDiscuzApi();
        $result = uc_user_login($email, $password, 2);
        if ($result[0] <= 0) {
            return $this->convertApiResult($result);
        }

        return array(
            'id' => $result[0],
            'username' => $result[1],
            'email' => $result[3],
            'createdTime' => '',
            'createdIp' => '',
        );
    }

    public function getAvatar($userId, $size = 'middle')
    {
        $this->initDiscuzApi();
        if (uc_check_avatar($userId)) {
            return UC_API."/avatar.php?uid=".$userId."&type=virtual&size=".$size;
        } else {
            return null;
        }
    }

    public function getProviderName()
    {
        return 'discuz';
    }

    private function initDiscuzApi()
    {
        require_once __DIR__ .'/../../../../../app/config/uc_client_config.php';
        require_once __DIR__ .'/../../../../../vendor_user/uc_client/client.php';
    }

    private function convertApiResult($result)
    {
        switch ($result) {
            case true:
                return array('success', '');
            case 0:
                return array('error_input', '输入不合法');
            case -1:
                return array('error_length_invalid', '名称不合法');
            case -2:
                return array('error_illegal_char', '名称含有非法字符');
            case -3:
                return array('error_duplicate', '名称已被注册');
            case -4:
                return array('error_illegal', 'Email格式不正确');
            case -5:
                return array('error_white_list', 'Email不允许注册');
            case -6:
                return array('error_duplicate', 'Email已存在');
            default:
                return array('error_unknown', '未知错误');
        }
    }
}