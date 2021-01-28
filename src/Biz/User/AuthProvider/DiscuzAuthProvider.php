<?php

namespace Biz\User\AuthProvider;

use Biz\BaseService;
use Biz\System\Service\SettingService;
use AppBundle\Common\Exception\UnexpectedValueException;

class DiscuzAuthProvider extends BaseService implements AuthProvider
{
    private $mockedDiscusClientPath = null;

    public function register($registration)
    {
        $this->initDiscuzApi();

        if (UC_CHARSET == 'gbk') {
            $registration['nickname'] = iconv('UTF-8', 'gb2312', $registration['nickname']);
        }

        $result = uc_user_register($registration['nickname'], $registration['password'], $registration['email']);
        if ($result < 0) {
            $result = $this->convertApiResult($result);
            throw new UnexpectedValueException("{$result[0]}:{$result[1]}");
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
        $this->initDiscuzApi();

        return uc_user_renameuser($userId, $newName);
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        $this->initDiscuzApi();
        $user = uc_get_user($userId, 1);
        $result = uc_user_edit($user[1], null, null, $newEmail, 1);

        return 1 == $result;
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $this->initDiscuzApi();
        $user = uc_get_user($userId, 1);
        $result = uc_user_edit($user[1], null, $newPassword, null, 1);

        return 1 == $result;
    }

    public function checkUsername($username)
    {
        $this->initDiscuzApi();

        if (UC_CHARSET == 'gbk') {
            $username = iconv('UTF-8', 'gb2312', $username);
        }

        $result = uc_user_checkname($username);

        return $this->convertApiResult($result);
    }

    public function checkEmail($email)
    {
        $this->initDiscuzApi();
        $result = uc_user_checkemail($email);

        return $this->convertApiResult($result);
    }

    public function checkMobile($mobile)
    {
        return array('success', '');
    }

    public function checkConnect()
    {
        $this->initDiscuzApi();
        try {
            uc_app_ls();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkPassword($userId, $password)
    {
        $this->initDiscuzApi();
        $result = uc_user_login($userId, $password, 1);

        return $result[0] > 0;
    }

    public function checkLoginById($userId, $password)
    {
        $this->initDiscuzApi();
        $result = uc_user_login($userId, $password, 1);
        if ($result[0] <= 0) {
            return null;
        }

        if (UC_CHARSET == 'gbk') {
            $result[1] = iconv('gb2312', 'UTF-8', $result[1]);
        }

        return array(
            'id' => $result[0],
            'nickname' => $result[1],
            'email' => $result[3],
            'createdTime' => '',
            'createdIp' => '',
        );
    }

    public function checkLoginByNickname($nickname, $password)
    {
        $this->initDiscuzApi();

        if (UC_CHARSET == 'gbk') {
            $nickname = iconv('UTF-8', 'gb2312', $nickname);
        }

        $result = uc_user_login($nickname, $password);
        if ($result[0] <= 0) {
            return null;
        }

        if (UC_CHARSET == 'gbk') {
            $result[1] = iconv('gb2312', 'UTF-8', $result[1]);
        }

        return array(
            'id' => $result[0],
            'nickname' => $result[1],
            'email' => $result[3],
            'createdTime' => '',
            'createdIp' => '',
        );
    }

    public function checkLoginByEmail($email, $password)
    {
        $this->initDiscuzApi();
        $result = uc_user_login($email, $password, 2);
        if ($result[0] <= 0) {
            return null;
        }

        if (UC_CHARSET == 'gbk') {
            $result[1] = iconv('gb2312', 'UTF-8', $result[1]);
        }

        return array(
            'id' => $result[0],
            'nickname' => $result[1],
            'email' => $result[3],
            'createdTime' => '',
            'createdIp' => '',
        );
    }

    public function getAvatar($userId, $size = 'middle')
    {
        $this->initDiscuzApi();
        if (uc_check_avatar($userId)) {
            return UC_API.'/avatar.php?uid='.$userId.'&type=virtual&size='.$size;
        } else {
            return null;
        }
    }

    public function getProviderName()
    {
        return 'discuz';
    }

    public function initDiscuzApi()
    {
        $setting = $this->getSettingService()->get('user_partner');
        $discuzConfig = $setting['partner_config']['discuz'];

        foreach ($discuzConfig as $key => $value) {
            define(strtoupper($key), $value);
        }

        if (empty($this->mockedDiscusClientPath)) {
            require_once __DIR__.'/../../../../vendor_user/uc_client/client.php';
        } else {
            require_once $this->mockedDiscusClientPath;
        }
    }

    protected function convertApiResult($result)
    {
        if ($result > 0) {
            return array('success', '');
        }

        switch ($result) {
            case 0:
                return array('error_input', '输入不合法');
            case -1:
                return array('error_length_invalid', '名称不合法,长度不符合关联论坛用户名要求');
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
