<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\UserService;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class UserServiceImpl extends BaseService implements UserService
{
    public function getVersion()
    {
        var_dump("CourseServiceImpl->getVersion");
        return $this->formData;
    }
    
    public function login()
    {
        $username = $this->getParam('_username');
        $password = $this->getParam('_password');
        $user     = $this->loadUserByUsername($this->request, $username);
        
        if (empty($user)) {
            return $this->createErrorResponse('username_error', '用户帐号不存在');
        }
        
        if (!$this->controller->getUserService()->verifyPassword($user['id'], $password)) {
            return $this->createErrorResponse('password_error', '帐号密码不正确');
        }
        
        $token = $this->controller->createToken($user, $this->request);
        
        $result = array(
            'token' => $token,
            'user' => $this->controller->filterUser($user)
        );
        
        $this->controller->getLogService()->info(MobileBaseController::MOBILE_MODULE, "user_login", "用户登录", array(
            "username" => $username
        ));
        return $result;
    }
    
    private function loadUserByUsername($request, $username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->controller->getUserService()->getUserByEmail($username);
        } else {
            $user = $this->controller->getUserService()->getUserByNickname($username);
        }
        
        if (empty($user)) {
            return null;
        }
        $user['currentIp'] = $request->getClientIp();
        
        return $user;
    }
}