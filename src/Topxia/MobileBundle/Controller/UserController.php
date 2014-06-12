<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\Common\SimpleValidator;

class UserController extends MobileController
{

    public function userAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);
        $user = $this->filterUser($user);
        return $this->createJson($request, $user);
    }

    public function loginAction(Request $request)
    {
        $username = $request->query->get('_username');
        $password = $request->query->get('_password');
        $user = $this->loadUserByUsername($request, $username);

        if (empty($user)) {
            return $this->createErrorResponse($request, 'username_error', '用户帐号不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            return $this->createErrorResponse($request, 'password_error', '帐号密码不正确');
        }

        $token = $this->createToken($user, $request);

        $result = array(
            'token' => $token,
            'user' => $this->filterUser($user),
        );

        return $this->createJson($request, $result);
    }

    public function logoutAction(Request $request)
    {
        $token = $request->query->get('token', '');
        $this->getUserService()->deleteToken(self::TOKEN_TYPE, $token);
        return $this->createJson($request, true);
    }

    public function loginWithTokenAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if (empty($token) or  $token['type'] != self::TOKEN_TYPE) {
            $token = null;
        }

        if (empty($token)) {
            $user = null;
        } else {
            $user = $this->getUserService()->getUser($token['userId']);
        }

        $site = $this->getSettingService()->get('site', array());

        $result = array(
            'token' => empty($token) ? '' : $token['token'],
            'user' => empty($user) ? null : $this->filterUser($user),
            'site' => $this->getSiteInfo($request)
        );
        
        return $this->createJson($request, $result);
    }


    public function loginWithSiteAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());
        $result = array(
            'site' => $this->getSiteInfo($request)
        );

        return $this->createJson($request, $result);
    }

    public function notifiactionsAction(Request $request)
    {
        $this->getUserToken($request);
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '您尚未登录！');
        }

        // 通知，只取最近的100条
        $notifications = $this->getNotificationService()->findUserNotifications($user['id'], 0, 100);
        $this->getNotificationService()->clearUserNewNotificationCounter($user['id']);

        foreach ($notifications as &$notification) {
            $notification['createdTime'] = date('c', $notification['createdTime']);
            unset($notification);
        }

        return $this->createJson($request, $notifications);
    }

    public function registerAction(Request $request)
    {
        $email = $request->get('email');
        $nickname = $request->get('nickname');
        $password = $request->get('password');

        if (!SimpleValidator::email($email)) {
            return $this->createErrorResponse($request, 'email_invalid', '邮箱地址格式不正确');
        }

        if (!SimpleValidator::nickname($nickname)) {
            return $this->createErrorResponse($request, 'nickname_invalid', '昵称格式不正确');
        }

        if (!SimpleValidator::password($password)) {
            return $this->createErrorResponse($request, 'password_invalid', '密码格式不正确');
        }

        if (!$this->getUserService()->isEmailAvaliable($email)) {
            return $this->createErrorResponse($request, 'email_exist', '该邮箱已被注册');
        }

        if (!$this->getUserService()->isNicknameAvaliable($nickname)) {
            return $this->createErrorResponse($request, 'nickname_exist', '该昵称已被注册');
        }

        $user = $this->getAuthService()->register(array(
            'email' => $email,
            'nickname' => $nickname,
            'password' => $password,
        ));

        $token = $this->createToken($user, $request);

        return $this->createJson($request, array (
            'user' => $this->filterUser($user),
            'token' => $token
        ));
    }

    private function loadUserByUsername ($request, $username) {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getUserService()->getUserByEmail($username);
        } else {
            $user = $this->getUserService()->getUserByNickname($username);
        }

        if (empty($user)) {
            return null;
        }
        $user['currentIp'] = $request->getClientIp();

        return $user;
    }

    protected function filterUser($user)
    {
        if (empty($user)) {
            return null;
        }

        $users = $this->filterUsers(array($user));

        return current($users);
    }

    public function filterUsers($users)
    {
        if (empty($users)) {
            return array();
        }

        $container = $this->container;

        return array_map(function($user) use ($container) {
            $user['smallAvatar'] = $container->get('topxia.twig.web_extension')->getFilePath($user['smallAvatar'], 'avatar.png', true);
            $user['mediumAvatar'] = $container->get('topxia.twig.web_extension')->getFilePath($user['mediumAvatar'], 'avatar.png', true);
            $user['largeAvatar'] = $container->get('topxia.twig.web_extension')->getFilePath($user['largeAvatar'], 'avatar-large.png', true);
            $user['createdTime'] = date('c', $user['createdTime']);

            $user['email'] = '';
            $user['roles'] = array();
            unset($user['password']);
            unset($user['salt']);
            unset($user['createdIp']);
            unset($user['loginTime']);
            unset($user['loginIp']);
            unset($user['loginSessionId']);
            unset($user['newMessageNum']);
            unset($user['newNotificationNum']);
            unset($user['promoted']);
            unset($user['promotedTime']);
            unset($user['approvalTime']);
            unset($user['approvalStatus']);
            unset($user['tags']);
            unset($user['point']);
            unset($user['coin']);

            return $user;
        }, $users);
    }

    public function getSchoolSiteAction(Request $request)
    {
        $mobile = $this->getSettingService()->get('mobile', array());

        return $this->createJson($request, array(
            'about' => $this->convertAbsoluteUrl($request, $mobile['about']), 
        ));
    }

    public function convertAbsoluteUrl($request, $html)
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) use ($baseUrl) {
            return "src=\"{$baseUrl}/{$matches[1]}\"";
        }, $html);

        return $html;

    }
    
    private function getSiteInfo($request)
    {
        $site = $this->getSettingService()->get('site', array());
        $mobile = $this->getSettingService()->get('mobile', array());

        if (!empty($mobile['logo'])) {
            $logo = $request->getSchemeAndHttpHost() . '/' . $mobile['logo'];
        } else {
            $logo = '';
        }

        $splashs = array();
        for($i=1; $i < 5; $i++) {
            if (!empty($mobile['splash'. $i])) {
                $splashs[] = $request->getSchemeAndHttpHost() . '/' . $mobile['splash'. $i];
            }
        }

        return array(
            'name' => $site['name'],
            'url' => $request->getSchemeAndHttpHost() . '/mapi_v1',
            'logo' => $logo,
            'splashs' => $splashs,
            'apiVersionRange' => array(
                "min" => "1.0.0",
                "max" => "1.0.0"
            ),
        );
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}
