<?php

namespace Topxia\MobileBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\WebBundle\Form\RegisterType;
use Topxia\Common\SimpleValidator;

class UserController extends MobileController
{

    public function __construct()
    {
        $this->setResultStatus();
    }

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
            return $this->createErrorResponse('username_error', '用户帐号不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            return $this->createErrorResponse('password_error', '帐号密码不正确');
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
        $this->getUserService()->deleteToken(UserController::$mobileType, $token);
        return $this->createJson($request, true);
    }

    public function loginWithTokenAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if (empty($token)) {
            return $this->createErrorResponse('token_error', '登录已过期，请重新登录');
        }

        if ($token['type'] != UserController::$mobileType) {
            return $this->createErrorResponse('token_error', '登录已过期，请重新登录');
        }

        $user = $this->getUserService()->getUser($token['userId']);
        if (empty($user)) {
            return $this->createErrorResponse('user_not_found', '用户不存在');
        }

        $result = array(
            'token' => $token['token'],
            'user' => $this->filterUser($user),
        );
        
        return $this->createJson($request, $result);
    }


    public function checkQRAction(Request $request)
    {
        $site = $this->getSettingService()->get('site', array());
        if($site) {
            $this->setResultStatus("success");
            $token = $this->getUserToken($request);
            if ($token) {
                $this->result["token"] = $token["token"];
                $user = $this->getUserService()->getUser($token["userId"]);
                $this->result["user"] = $this->changeUserPicture($user, false);
            }

            $site['url'] = MobileController::$baseUrl;
            $this->result["school"] = array(
                "title"=>$site['name'],
                "info"=>$site['slogan'],
                "url"=>$site['url'],
                "logo"=>$site['logo']
            );
        }
        
        return $this->createJson($request, $this->result);
    }
    
    public function getNoticeAction(Request $request)
    {
        $token = $this->getUserToken($request);
        if ($token) {
            $user = $this->getCurrentUser();
            if (!$user) {
                throw $this->createAccessDeniedException();
            }
            $page = $this->getParam($request, 'page', 0);
            $count = $this->getNotificationService()->getUserNotificationCount($token['userId']);
            $notifications = $this->getNotificationService()->findUserNotifications(
                $token['userId'],
                $page,
                MobileController::$defLimit
            );

            $notifications = $this->changeCreatedTime($notifications);
            $this->setResultStatus("success");
            $this->result['notifications'] = $notifications;
            $this->result = $this->setPage($this->result, $page, $count);
            $this->getNotificationService()->clearUserNewNotificationCounter($token['userId']);
        }
        return $this->createJson($request, $this->result);
    }

    public function registUserAction(Request $request)
    {
        $registration = array(
            "email"=>$request->query->get('email'),
            "password"=>$request->query->get('password'),
            "nickname"=>$request->query->get('nickname'),
        );

        $vaildResult = $this->vaildRegistration($registration);
        if (empty($vaildResult)) {
            $registration['createdIp'] = $request->getClientIp();
            if (!$this->getUserService()->isNicknameAvaliable($registration['nickname'])) {
                $this->result['message'] = "昵称已存在";
            }
            if (!$this->getUserService()->isEmailAvaliable($registration['email'])) {
                $this->result['message'] = "邮箱已注册";
            }
            if (!isset($result['message'])) {
                $user = $this->getAuthService()->register($registration);
                $this->authenticateUser($user);
                $this->sendRegisterMessage($user);
                $this->result['token'] = $token = $this->createToken($user, $request);
                $this->setResultStatus("success");
            }
        } else {
            $result['message'] = $vaildResult['message'];
        }
        
        return $this->createJson($request, $this->result);
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

    protected function vaildRegistration($registration)
    {
        $msg = null;
        $result = null;
        if (!SimpleValidator::email($registration['email'])) {
            $msg= "邮箱格式不正确";
        }else if (!SimpleValidator::nickname($registration['nickname'])) {
            $msg = "昵称格式不正确";
        } else if (!SimpleValidator::password($registration['password'])) {
            $msg = "密码格式不正确";
        }
        if ($msg) {
            $result = array("message"=>$msg);
        }
        return $result;
    }

    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
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

}
