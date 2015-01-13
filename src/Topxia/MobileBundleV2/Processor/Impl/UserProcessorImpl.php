<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\UserProcessor;
use Topxia\Common\SimpleValidator;
use Topxia\MobileBundleV2\Controller\MobileBaseController;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Form\MessageReplyType;

class UserProcessorImpl extends BaseProcessor implements UserProcessor
{
    public function getVersion()
    {
        var_dump($this->request->get("name"));
        return $this->formData;
    }
    
    public function getUserCoin()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (empty($user)) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $coinEnabled = $this->controller->setting("coin.coin_enabled");
        if(empty($coinEnabled) || $coinEnabled == 0) {
            return $this->createErrorResponse('error', "网校虚拟币未开启！");
        }

        $account = $this->getCashAccountService()->getAccountByUserId($user->id,true);
        
        if(empty($account)){
            $account = $this->getCashAccountService()->createAccount($user->id);
        }

        return $account;
    }

    public function sendMessage()
    {
        $content = $this->getParam("content");
        $fromId = $this->getParam("fromId");
        $conversationId = $this->getParam("conversationId");

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $message = $this->getMessageService()->sendMessage($user['id'], $fromId, $content);
        $toId = $message['toId'];
        $nickname = $user['nickname'];
        //PushService::sendMsg("$toId","0|$fromId|$nickname|$conversationId");
        return $message;
    }

    public function getMessageList()
    {
        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);
        $conversationId = $this->getParam("conversationId");
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) or $conversation['toId'] != $user['id']) {
            throw $this->createNotFoundException('私信会话不存在！');
        }

        $this->getMessageService()->markConversationRead($conversationId);

        $messages = $this->getMessageService()->findConversationMessages(
            $conversation['id'], 
            $start,
            $limit
        );
        usort($messages, function($a, $b){
            $aId = $a["id"];
            $bId = $b["id"];
            if ($aId == $bId) {
                return 0;
            }
            return ($aId > $bId) ? 1 : -1;
        });

        $controller = $this->controller;
        $messages = array_map(function($message) use ($controller){
            $message['createdTime'] = date('c',$message['createdTime']);
            $message["createdUser"] = $controller->filterUser($message["createdUser"]);
            return $message;
        }, $messages);
        return $messages;
        return $this->render('TopxiaWebBundle:Message:conversation-show.html.twig',array(
            'conversation'=>$conversation, 
            'messages'=>$messages, 
            'receiver'=>$this->getUserService()->getUser($conversation['fromId']),
            'form' => $form->createView(),
            'paginator' => $paginator
        ));
    }

    public function getUserMessages()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $start = (int) $this->getParam("start", 0);
        $limit = (int) $this->getParam("limit", 10);
        $conversations = $this->getMessageService()->findUserConversations(
            $user->id,
            $start,
            $limit
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));
        $users = $this->controller->filterUsers($users);
        $this->getMessageService()->clearUserNewMessageCounter($user['id']);

        $conversations = array_map(function($conversation) use ($users){
            $conversation['latestMessageTime'] = date('c',$conversation['latestMessageTime']);
            $conversation['createdTime'] = date('c',$conversation['createdTime']);
            $conversation['user'] = $users[$conversation['fromId']];
            return $conversation;
        }, $conversations);
        return $conversations;
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
            ))->getContent();

        $message = preg_replace_callback('/<[\\/]?li[^>]*>/', function($matches) {
            return "";
        }, $message);
        return $message;
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
        $user['following'] = $this->controller->getUserService()->findUserFollowingCount($userId);
        $user['follower'] = $this->controller->getUserService()->findUserFollowerCount($user['id']);
        return $this->controller->filterUser($user);
    }

    public function logout()
    {
        $token = $this->controller->getToken($this->request);
        if (! empty($token)) {
            $user = $this->controller->getUserByToken($this->request);
            $this->log("user_logout", "用户退出",  array(
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
        $this->log("user_regist", "用户注册",  array(
                "user" => $user)
            );
        return array (
            'user' => $this->controller->filterUser($user),
            'token' => $token
        );
    }

    public function loginWithToken()
    {
        $version = $this->request->query->get('version', 1);
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
            'site' => $this->getSiteInfo($this->request, $version)
        );
        $result['user']['following'] = $this->controller->getUserService()->findUserFollowingCount($user['id']);
        $result['user']['follower'] = $this->controller->getUserService()->findUserFollowerCount($user['id']);
        $this->log("user_login", "用户二维码登录",  array(
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

        $result['user']['following'] = $this->controller->getUserService()->findUserFollowingCount($user['id']);
        $result['user']['follower'] = $this->controller->getUserService()->findUserFollowerCount($user['id']);
        
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

    public function getFollowings(){
        $user = $this->controller->getUserByToken($this->request);
        $start = $this->getParam('start',0);
        $limit = $this->getParam('limit',10);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能查看关注");
        }
        $followings = $this->controller->getUserService()->findUserFollowing($user['id'], $start, $limit);
        $result = array();
        $index = 0;
        foreach ($followings as $key => $following) {
            $following['smallAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($following['smallAvatar'], 'course-large.png', true);
            $following['mediumAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($following['mediumAvatar'], 'course-large.png', true);
            $following['largeAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($following['largeAvatar'], 'course-large.png', true);
            $result[$index++] = $following;

        }
        return $result;
    }

    public function getFollowers(){
        $user = $this->controller->getUserByToken($this->request);
        $start = $this->getParam('start',0);
        $limit = $this->getParam('limit',10);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，不能查看关注");
        }
        $followers = $this->controller->getUserService()->findUserFollowers($user['id'], $start, $limit);
        $result = array();
        $index = 0;
        foreach ($followers as $key => $follower) {
            $follower['smallAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($follower['smallAvatar'], 'course-large.png', true);
            $follower['mediumAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($follower['mediumAvatar'], 'course-large.png', true);
            $follower['largeAvatar'] = $this->controller->getContainer()->get('topxia.twig.web_extension')->getFilePath($follower['largeAvatar'], 'course-large.png', true);
            $result[$index++] = $follower;
        }
        return $result;
    }

    public function follow(){
        $user = $this->controller->getUserByToken($this->request);
        $toId = $this->getParam('toId');
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        $result = $this->controller->getUserService()->follow($user['id'], $toId);

        $userShowUrl = $this->controller->generateUrl('user_show', array('id' => $user['id']), true);
        $message = "用户<a href='{$userShowUrl}' target='_blank'>{$user['nickname']}</a>已经关注了你！";
        $this->controller->getNotificationService()->notify($toId, 'default', $message);

        return $result;
    }

    public function unfollow(){
        $user = $this->controller->getUserByToken($this->request);
        $toId = $this->getParam('toId');
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->controller->getUserService()->unFollow($user['id'], $toId);

        $userShowUrl = $this->controller->generateUrl('user_show', array('id' => $user['id']), true);
        $message = "用户<a href='{$userShowUrl}' target='_blank'>{$user['nickname']}</a>对你已经取消了关注！";
        $this->getNotificationService()->notify($toId, 'default', $message);

        return $result;
    }
}