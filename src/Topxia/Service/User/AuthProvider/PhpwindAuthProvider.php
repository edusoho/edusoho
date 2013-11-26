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

    public function syncLogout()
    {
         $api = $this->getWindidApi('user');
         return $api->synLogout($userId);
    }

    public function changeUsername($userId, $newName)
    {
        return true;
    }

    public function changeEmail($userId, $newEmail)
    {
        return true;
    }

    public function changePassowrd($userId, $newPassword)
    {
        return true;
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
        return false;
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
            'username' => $apiUser['username'],
            'email' => $apiUser['email'],
            'createdTime' => $apiUser['regdate'],
            'createdIp' => $apiUser['regip'],
        );
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
            case \WindidError::FAIL:
            default:
                return array('error_unknown', '未知错误');
        }
    }

}