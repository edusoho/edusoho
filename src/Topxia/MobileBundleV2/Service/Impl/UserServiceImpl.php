<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\UserService;
use Topxia\Common\SimpleValidator;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class UserServiceImpl extends BaseService implements UserService
{
    public function getVersion()
    {
        var_dump($this->request->get("name"));
        return $this->formData;
    }
    
    public function getUserLastlearning()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $courses = $this->getCourseService()->findUserLearnCourses($user['id'], 0, 1);

        if (!empty($courses)) {
            foreach ($courses as $course) {
                $member = $this->controller->getCourseService()->getCourseMember($course['id'], $user['id']);
            }

            $progress = $this->calculateUserLearnProgress($course, $member);
        } else {
            $course = array();
            $progress = array();
        }

        return array(
            'data' => $this->controller->filterCourse($course),
            'progress'  => $progress
            );
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100) . '%';

        return array (
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum']
        );
    }

    public function getUserNotification()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);

        $total = $this->getNotificationService()->getUserNotificationCount($user['id']);
        $this->getNotificationService()->clearUserNewNotificationCounter($user['id']);
        $notifications = $this->getNotificationService()->findUserNotifications(
            $user['id'],
            $start,
            $limit
        );

        foreach ($notifications as &$notification) {
            $notification['createdTime'] = date('c', $notification['createdTime']);
            $notification["message"] = $this->coverNotifyContent($notification);
            unset($notification);
        }
        return array(
            "start"=>$start,
            "total"=>$total,
            "limit"=>$limit,
            "data"=>$notifications);
    }

    private function coverNotifyContent($notification)
    {
        $message = "";
        $type = $notification['type'];

        $message = $this->controller->render("TopxiaWebBundle:Notification:item-" .$type. ".html.twig", array(
            "notification"=>$notification
            ));
        return $message->getContent();
    }
    public function getUserInfo()
    {
        $userId = $this->getParam('userId');
        $user = $this->controller->getUserService()->getUser($userId);
        if (empty($user)) {
            return array();
        }
        $userProfile = $this->controller->getUserService()->getUserProfile($userId);
        $userProfile = $this->filterUserProfile($userProfile);
        $user = array_merge($user, $userProfile);
        return $this->controller->filterUser($user);
    }

    public function logout()
    {
        $token = $this->controller->getToken($this->request);
        if (! empty($token)) {
            $user = $this->controller->getUserByToken($this->request);
            $this->controller->getLogService()->info(MobileBaseController::MOBILE_MODULE, "user_logout", "用户退出",  array(
                "userToken" => $user)
            );
        }
        $this->controller->getUserService()->deleteToken(MobileBaseController::TOKEN_TYPE, $token);
        return true;
    }

    private function filterUserProfile($userProfile)
    {
        foreach ($userProfile as $key => $value) {
            if (stripos($key, "intField") === 0 || stripos($key, "dateField") === 0) {
                unset($userProfile[$key]);
                continue;
            }
            if (stripos($key, "textField") === 0) {
                unset($userProfile[$key]);
                continue;
            }
            if (stripos($key, "floatField") === 0 || stripos($key, "varcharField") === 0) {
                unset($userProfile[$key]);
                continue;
            }
        }
        return $userProfile;
    }

    public function regist()
    {
        $email = $this->getParam('email');
        $nickname = $this->getParam('nickname');
        $password = $this->getParam('password');

        if (!SimpleValidator::email($email)) {
            return $this->createErrorResponse('email_invalid', '邮箱地址格式不正确');
        }

        if (!SimpleValidator::nickname($nickname)) {
            return $this->createErrorResponse('nickname_invalid', '昵称格式不正确');
        }

        if (!SimpleValidator::password($password)) {
            return $this->createErrorResponse('password_invalid', '密码格式不正确');
        }

        if (!$this->controller->getUserService()->isEmailAvaliable($email)) {
            return $this->createErrorResponse('email_exist', '该邮箱已被注册');
        }

        if (!$this->controller->getUserService()->isNicknameAvaliable($nickname)) {
            return $this->createErrorResponse('nickname_exist', '该昵称已被注册');
        }

        $user = $this->controller->getAuthService()->register(array(
            'email' => $email,
            'nickname' => $nickname,
            'password' => $password,
        ));

        $token = $this->controller->createToken($user, $this->request);

        return array (
            'user' => $this->controller->filterUser($user),
            'token' => $token
        );
    }

    public function loginWithToken()
    {
        $mobile = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '没有搜索到该网校！');
        }

        $token = $this->controller->getUserToken($this->request);
        if ($token == null ||  $token['type'] != MobileBaseController::TOKEN_TYPE) {
            $token = null;
        }

        if (empty($token)) {
            $user = null;
        } else {
            $user = $this->controller->getUserService()->getUser($token['userId']);
        }

        $site = $this->controller->getSettingService()->get('site', array());

        $result = array(
            'token' => empty($token) ? '' : $token['token'],
            'user' => empty($user) ? null : $this->controller->filterUser($user),
            'site' => $this->getSiteInfo($this->request)
        );
        
        $this->controller->getLogService()->info(MobileBaseController::MOBILE_MODULE, "user_login", "用户二维码登录",  array(
            "userToken" => $token)
        );

        return $result;
    }

    public function login()
    {
        $username = $this->getParam('_username');
        $password = $this->getParam('_password');
        $user  = $this->loadUserByUsername($this->request, $username);
        
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