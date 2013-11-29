<?php
namespace Topxia\Service\User\AuthProvider;

class PhpwindAuthProvider implements AuthProvider
{
    public function register($registration)
    {
        $api = $this->getWindidApi('user');

        $result = $api->register($registration['nickname'], $registration['email'], $registration['password']);
        if ($result < 1) {
            $result = $this->convertApiResult($result);
            throw new \RuntimeException("{$result[0]}:{$result[1]}");
        }

        $registration['id'] = $result;

        return $registration;
    }

    public function syncLogin($userId)
    {
        $api = $this->getWindidApi('user');
        return $api->synLogin($userId);
    }

    public function syncLogout($userId)
    {
        $api = $this->getWindidApi('user');
        return $api->synLogout($userId);
    }

    public function changeNickname($userId, $newName)
    {
        $api = $this->getWindidApi('user');
        $result = $api->editUser($userId, null, array('username' => $newName));
        return $result == 1;
    }

    public function changeEmail($userId, $password, $newEmail)
    {
        $api = $this->getWindidApi('user');
        $result = $api->editUser($userId, null, array('email' => $newEmail));
        return $result == 1;
    }

    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $api = $this->getWindidApi('user');
        $result = $api->editUser($userId, $oldPassword, array('password' => $newPassword));
        return $result == 1;
    }

    public function checkUsername($username)
    {
        $api = $this->getWindidApi('user');

        // 1: check username.
        $result = $api->checkUserInput($username, 1);

        return $this->convertApiResult($result);
    }

    public function checkEmail($email)
    {
        $api = $this->getWindidApi('user');

        // 1: check nickname.
        $result = $api->checkUserInput($email, 3);

        return $this->convertApiResult($result);
    }

    public function checkPassword($userId, $password)
    {
        $api = $this->getWindidApi('user');
        list($result, $apiUser) = $api->login($userId, $password, 1);
        return $result == 1;
    }

    public function checkLoginById($userId, $password)
    {
        $api = $this->getWindidApi('user');

        list($result, $apiUser) = $api->login($userId, $password, 1);
        if ($result != 1) {
            return null;
        }

        return array(
            'id' => $apiUser['uid'],
            'nickname' => $apiUser['username'],
            'email' => $apiUser['email'],
            'createdTime' => $apiUser['regdate'],
            'createdIp' => $apiUser['regip'],
        );
    }


    public function checkLoginByNickname($nickname, $password)
    {
        $api = $this->getWindidApi('user');

        list($result, $apiUser) = $api->login($nickname, $password, 2);
        if ($result != 1) {
            return null;
        }

        return array(
            'id' => $apiUser['uid'],
            'nickname' => $apiUser['username'],
            'email' => $apiUser['email'],
            'createdTime' => $apiUser['regdate'],
            'createdIp' => $apiUser['regip'],
        );
    }

    public function checkLoginByEmail($email, $password)
    {
        $api = $this->getWindidApi('user');

        list($result, $apiUser) = $api->login($email, $password, 3);
        if ($result != 1) {
            return null;
        }

        return array(
            'id' => $apiUser['uid'],
            'nickname' => $apiUser['username'],
            'email' => $apiUser['email'],
            'createdTime' => $apiUser['regdate'],
            'createdIp' => $apiUser['regip'],
        );
    }

    public function getAvatar($userId, $size = 'middle')
    {
        $api = $this->getWindidApi('avatar');
        $url = $api->getAvatar($userId, $size);

        if ($this->checkUrlExist($url)) {
            return $url;
        }

        return null;
    }

    public function getProviderName()
    {
        return 'phpwind';
    }

    private function getWindidApi($name)
    {
        define('WEKIT_TIMESTAMP', time());
        require_once __DIR__ .'/../../../../../vendor_user/windid_client/src/windid/WindidApi.php';
        return \WindidApi::api($name);
    }

    private function convertApiResult($result)
    {
        switch ($result) {
            case \WindidError::SUCCESS:
                return array('success', '');
            case \WindidError::NAME_EMPTY:
                return array('error_empty_name', '名称为空');
            case \WindidError::NAME_LEN:
                return array('error_length_invalid', '名称长度不符合');
            case \WindidError::NAME_ILLEGAL_CHAR:
                return array('error_illegal_char', '名称含有非法字符');
            case \WindidError::NAME_FORBIDDENNAME:
                return array('error_forbidden_name', '名称含有禁用字符');
            case \WindidError::NAME_DUPLICATE:
                return array('error_duplicate', '名称已被注册');
            case \WindidError::EMAIL_EMPTY:
                return array('error_empty', 'Email为空为空');
            case \WindidError::EMAIL_ILLEGAL:
                return array('error_illegal', 'Email格式不正确');
            case \WindidError::EMAIL_WHITE_LIST:
                return array('error_white_list', 'Email不在白名单');
            case \WindidError::EMAIL_BLACK_LIST:
                return array('error_black_list', 'Email处在黑名单');
            case \WindidError::EMAIL_DUPLICATE:
                return array('error_duplicate', 'Email已存在');
            case \WindidError::PASSWORD_LEN:
                return array('error_password_length_invalid', '密码长度不符合');
            case \WindidError::PASSWORD_ILLEGAL_CHAR:
                return array('error_password_illegal_char', '密码含有非法字符');
            case \WindidError::PASSWORD_ERROR:
                return array('error_password_error', '原密码错误');
            case \WindidError::FAIL:
            default:
                return array('error_unknown', '未知错误');
        }
    }

    public function checkUrlExist($url)
    {
        $headers = get_headers($url);
        return strpos($headers[0], ' 200 ') > 0;
    }

}