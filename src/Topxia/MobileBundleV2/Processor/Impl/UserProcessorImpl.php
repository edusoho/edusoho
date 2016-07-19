<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\SimpleValidator;
use Topxia\Common\ExtensionManager;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\UserProcessor;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class UserProcessorImpl extends BaseProcessor implements UserProcessor
{
    public function getVersion()
    {
        return $this->formData;
    }

    public function uploadAvatar()
    {
        $groupCode = 'tmp';
        $user      = $this->controller->getUserByToken($this->request);
        if (empty($user)) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $file = $this->request->files->get('file');
        if (empty($file)) {
            return $this->createErrorResponse('error', "没有选择上传文件!");
        }
        if (!FileToolkit::isImageFile($file)) {
            return $this->createErrorResponse('error', "您上传的不是图片文件，请重新上传。");
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        if (empty($record)) {
            return $this->createErrorResponse('error', "上传失败 ～请重新尝试!");
        }

        $host                  = $this->request->getSchemeAndHttpHost();
        $record['url']         = $host.$this->controller->get('topxia.twig.web_extension')->getFilePath($record['uri']);
        $record['createdTime'] = date('c', $record['createdTime']);
        unset($record['uri']);
        return $record;
    }

    public function updateUserProfile()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (empty($user)) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $profile = $this->request->request->get('profile');

        try {
            $fileId = $this->getParam("fileId", 0);
            $this->updateNickname($user, $profile['nickname']);
            $this->updateUserAvatar($user, $fileId);
            $this->getUserService()->updateUserProfile($user['id'], $profile);

            $user = $this->getUserService()->getUser($user['id']);
            return $this->controller->filterUser($user);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }
    }

    private function createImgCropOptions($naturalSize, $scaledSize)
    {
        $options = array();

        $options['x']  = 0;
        $options['y']  = 0;
        $options['x2'] = $scaledSize->getWidth();
        $options['y2'] = $scaledSize->getHeight();
        $options['w']  = $naturalSize->getWidth();
        $options['h']  = $naturalSize->getHeight();

        $options['imgs']           = array();
        $options['imgs']['large']  = array(200, 200);
        $options['imgs']['medium'] = array(120, 120);
        $options['imgs']['small']  = array(48, 48);
        $options['width']          = $naturalSize->getWidth();
        $options['height']         = $naturalSize->getHeight();

        return $options;
    }

    private function updateUserAvatar($user, $fileId)
    {
        if (empty($fileId)) {
            return;
        }
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        $options = $this->createImgCropOptions($naturalSize, $scaledSize);
        $record  = $this->getFileService()->getFile($fileId);
        if (empty($record)) {
            throw new \RuntimeException("Error file not exists");
        }
        $parsed = $this->getFileService()->parseFileUri($record['uri']);

        $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options);

        $fields = array();
        foreach ($filePaths as $key => $value) {
            $file     = $this->getFileService()->uploadFile("user", new File($value));
            $fields[] = array(
                "type" => $key,
                "id"   => $file['id']
            );
        }

        if (isset($options["deleteOriginFile"]) && $options["deleteOriginFile"] == 0) {
            $fields[] = array(
                "type" => "origin",
                "id"   => $record['id']
            );
        } else {
            $this->getFileService()->deleteFileByUri($record["uri"]);
        }

        if (empty($fields)) {
            throw new \RuntimeException("Error uplaod avatar");
        }
        $this->getUserService()->changeAvatar($user['id'], $fields);
    }

    private function updateNickname($user, $nickname)
    {
        $isNickname  = $this->getSettingService()->get('user_partner');
        $oldNickname = $user['nickname'];
        if ($oldNickname == $nickname) {
            return;
        }
        if ($isNickname['nickname_enabled'] == 0) {
            throw new \RuntimeException("网校设置不能修改昵称!");
        }

        $this->getAuthService()->changeNickname($user['id'], $nickname);
    }

    public function getUserCoin()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (empty($user)) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $coinEnabled = $this->controller->setting("coin.coin_enabled");
        if (empty($coinEnabled) || $coinEnabled == 0) {
            return $this->createErrorResponse('error', "网校虚拟币未开启！");
        }

        $account = $this->getCashAccountService()->getAccountByUserId($user->id, true);

        if (empty($account)) {
            $account = $this->getCashAccountService()->createAccount($user->id);
        }

        return $account;
    }

    public function sendMessage()
    {
        $content        = $this->getParam("content");
        $fromId         = $this->getParam("fromId");
        $conversationId = $this->getParam("conversationId");

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $user = $this->controller->getUserService()->getUser($user['id']);

        $message  = $this->getMessageService()->sendMessage($user['id'], $fromId, $content);
        $toId     = $message['toId'];
        $nickname = $user['nickname'];
        //PushService::sendMsg("$toId","0|$fromId|$nickname|$conversationId");
        $message['createdUser'] = $this->controller->filterUser($user);
        return $message;
    }

    public function getMessageList()
    {
        $start          = (int) $this->getParam("start", 0);
        $limit          = (int) $this->getParam("limit", 10);
        $conversationId = $this->getParam("conversationId");
        $user           = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            throw $this->createNotFoundException('私信会话不存在！');
        }

        $this->getMessageService()->markConversationRead($conversationId);

        $messages = $this->getMessageService()->findConversationMessages(
            $conversation['id'],
            $start,
            $limit
        );
        usort($messages, function ($a, $b) {
            $aId = $a["id"];
            $bId = $b["id"];
            if ($aId == $bId) {
                return 0;
            }
            return ($aId > $bId) ? 1 : -1;
        });

        $controller = $this->controller;
        $messages   = array_map(function ($message) use ($controller) {
            $message['createdTime'] = date('c', $message['createdTime']);
            $message["createdUser"] = $controller->filterUser($message["createdUser"]);
            return $message;
        }, $messages);
        return $messages;
        return $this->render('TopxiaWebBundle:Message:conversation-show.html.twig', array(
            'conversation' => $conversation,
            'messages'     => $messages,
            'receiver'     => $this->getUserService()->getUser($conversation['fromId']),
            'form'         => $form->createView(),
            'paginator'    => $paginator
        ));
    }

    public function getUserMessages()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录！");
        }

        $start         = (int) $this->getParam("start", 0);
        $limit         = (int) $this->getParam("limit", 10);
        $conversations = $this->getMessageService()->findUserConversations(
            $user->id,
            $start,
            $limit
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));
        $users = $this->controller->filterUsers($users);
        $this->getMessageService()->clearUserNewMessageCounter($user['id']);

        $conversations = array_map(function ($conversation) use ($users) {
            $conversation['latestMessageTime'] = date('c', $conversation['latestMessageTime']);
            $conversation['createdTime']       = date('c', $conversation['createdTime']);
            $conversation['user']              = $users[$conversation['fromId']];
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
            $course   = array();
            $progress = array();
        }

        return array(
            'data'     => $this->controller->filterCourse($course),
            'progress' => $progress
        );
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $member['learnedNum'],
            'total'   => $course['lessonNum']
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
        //$this->getNotificationService()->clearUserNewNotificationCounter($user['id']);
        $notifications = $this->getNotificationService()->findUserNotifications(
            $user['id'],
            $start,
            $limit
        );

        foreach ($notifications as &$notification) {
            $notification["message"] = $this->coverNotifyContent($notification);
            unset($notification);
        }
        return array(
            "start" => $start,
            "total" => $total,
            "limit" => $limit,
            "data"  => $notifications);
    }

    private function coverNotifyContent($notification)
    {
        $message = "";
        $type    = $notification['type'];
        $manager = ExtensionManager::instance();
        $message = $manager->renderNotification($notification);

        $message = preg_replace_callback('/<div class=\"([\\w-]+)\">([^>]*)<\/div>/', function ($matches) {
            $content   = $matches[2];
            $className = $matches[1];
            if ($className == "notification-footer") {
                return "<br><br><font color=#CFCFCF><fontsize>".$content."</fontsize></font>";
            }
            return $content;
        }, $message);

        $message = str_replace("div", "span", $message);
        return $message;
    }

    public function getUserInfo()
    {
        $userId = $this->getParam('userId');
        $user   = $this->controller->getUserService()->getUser($userId);
        if (empty($user)) {
            return array();
        }
        $userProfile = $this->controller->getUserService()->getUserProfile($userId);
        $userProfile = $this->filterUserProfile($userProfile);
        $user        = array_merge($user, $userProfile);
        return $this->controller->filterUser($user);
    }

    public function logout()
    {
        $token = $this->controller->getToken($this->request);
        if (!empty($token)) {
            $user = $this->controller->getUserByToken($this->request);
            $this->log("user_logout", "用户退出", array(
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

    public function smsSend()
    {
        $phoneNumber = $this->getParam('phoneNumber');
        try {
            $this->request->request->set('to', $phoneNumber);
            $this->request->request->set('sms_type', 'sms_registration');
            $response = $this->controller->forward('TopxiaWebBundle:EduCloud:smsSend', array());
            $content  = $response->getContent();
            $content  = json_decode($content);
            if (!empty($content) && isset($content->error)) {
                return $this->createErrorResponse('error', $content->error);
            }
            return array('code' => '200', 'msg' => "发送成功");
        } catch (Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return $this->createErrorResponse('', "GET method");
    }

    private function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);
        for ($i = 1; $i < $length; $i++) {
            $code = $code.rand(0, 9);
        }
        return $code;
    }

    private function checkPhoneNum($num)
    {
        return preg_match("/^1\d{10}$/", $num);
    }

    private function checkLastTime($smsLastTime, $currentTime, $allowedTime = 120)
    {
        if (!((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime))) {
            return false;
        }
        return true;
    }

    private function getCloudSmsKey($key)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        if (isset($setting[$key])) {
            return $setting[$key];
        }
        return null;
    }

    private function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password'))) {
            throw new \RuntimeException('不存在的sms Type');
        }

        if ((!$user->isLogin()) && (in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_forget_pay_password')))) {
            throw new \RuntimeException('用户未登录');
        }

        if ($this->getCloudSmsKey("cloud_sms.{$smsType}") != 'on') {
            throw new \RuntimeException('网站未开启该使用场景短信验证');
        }
    }

    public function regist()
    {
        $email       = $this->getParam('email');
        $password    = $this->getParam('password');
        $nickname    = $this->getParam('nickname');
        $phoneNumber = $this->getParam('phone');
        $smsCode     = $this->getParam('smsCode');

        $result = array('meta' => null);

        $auth = $this->getSettingService()->get('auth', array());
        if (isset($auth['register_mode']) && $auth['register_mode'] == 'closed') {
            return $this->createErrorResponse('register_closed', '系统暂时关闭注册，请联系管理员');
        }

        if (!$nickname) {
            $nickname = "ES".time();
            while (!$this->controller->getUserService()->isNicknameAvaliable($nickname)) {
                $nickname = "ES".time();
            }
        } else {
            if (!$this->controller->getUserService()->isNicknameAvaliable($nickname)) {
                return $this->createErrorResponse('nickname_exist', '该昵称已被注册');
            }
        }

        $user = null;

        if (!empty($email)) {
            if (!SimpleValidator::email($email)) {
                return $this->createErrorResponse('email_invalid', '邮箱地址格式不正确');
            }
            if (!$this->controller->getUserService()->isEmailAvaliable($email)) {
                return $this->createErrorResponse('email_exist', '该邮箱已被注册');
            }
            if (!SimpleValidator::password($password)) {
                return $this->createErrorResponse('password_invalid', '密码格式不正确');
            }
            $registTypeName = $auth['register_mode'] == "email" ? "email" : "emailOrMobile";
            try {
                $user = $this->controller->getAuthService()->register(array(
                    $registTypeName => $email,
                    'nickname'      => $nickname,
                    'password'      => $password,
                    'createdIp'     => $this->request->getClientIp()
                ));
            } catch (\Exception $e) {
                return $this->createErrorResponse('register_invalid', $e->getMessage());
            }
        } else {
            if (!$this->checkPhoneNum($phoneNumber)) {
                return $this->createErrorResponse('phone_invalid', '手机号格式不正确');
            }
            if (!$this->getUserService()->isMobileUnique($phoneNumber)) {
                return $this->createErrorResponse('phone_exist', '该手机号码已被其他用户绑定');
            }
            if ($this->controller->setting('cloud_sms.sms_enabled') == '1') {
                $requestInfo                 = array('sms_code' => $smsCode, 'mobile' => $phoneNumber);
                list($result, $sessionField) = $this->smsCheck($this->request, $requestInfo, 'sms_registration');
                if ($result) {
                    $registTypeName = $auth['register_mode'] == "mobile" ? "mobile" : "emailOrMobile";
                    try {
                        $user = $this->controller->getAuthService()->register(array(
                            $registTypeName => $sessionField['to'],
                            'nickname'      => $nickname,
                            'password'      => $password,
                            'createdIp'     => $this->request->getClientIp()
                        ));
                    } catch (\Exception $e) {
                        return $this->createErrorResponse('register_invalid', $e->getMessage());
                    }

                    $this->clearSmsSession($this->request, 'sms_registration');
                } else {
                    return $this->createErrorResponse('sms_invalid', '手机短信验证错误，请重新注册');
                }
            }
        }

        if ($nickname && !SimpleValidator::nickname($nickname)) {
            return $this->createErrorResponse('nickname_invalid', '昵称格式不正确');
        }

        $token = $this->controller->createToken($user, $this->request);
        if (!empty($user) && !isset($user["currentIp"])) {
            $user["currentIp"] = "127.0.0.1";
        }
        $this->log("user_regist", "用户注册", array("user" => $user));

        return array(
            'user'  => $this->controller->filterUser($user),
            'token' => $token
        );
    }

    private function smsCheck($request, $mobileInfo, $scenario)
    {
        $sessionField             = $request->getSession()->get($scenario);
        $sessionField['sms_type'] = $scenario;

        $requestField['sms_code'] = $mobileInfo['sms_code'];
        $requestField['mobile']   = $mobileInfo['mobile'];

        $result = $this->checkSms($sessionField, $requestField, $scenario);
        return array($result, $sessionField);
    }

    private function checkSms($sessionField, $requestField, $scenario, $allowedTime = 1800)
    {
        $smsType = $sessionField['sms_type'];
        if ((strlen($smsType) == 0) || (strlen($scenario) == 0)) {
            return false;
        }
        if ($smsType != $scenario) {
            return false;
        }

        $currentTime = time();
        $smsLastTime = isset($sessionField['sms_last_time']) ? $sessionField['sms_last_time'] : 0;
        if ((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime)) {
            return false;
        }

        $smsCode       = $sessionField['sms_code'];
        $smsCodePosted = $requestField['sms_code'];
        if ((strlen($smsCodePosted) == 0) || (strlen($smsCode) == 0)) {
            return false;
        }

        if ($smsCode != $smsCodePosted) {
            return false;
        }

        $toMobile = $sessionField['to'];
        $mobile   = $requestField['mobile'];
        if ((strlen($toMobile) == 0) || (strlen($mobile) == 0)) {
            return false;
        }
        if ($toMobile != $mobile) {
            return false;
        }

        return true;
    }

    private function clearSmsSession($request, $scenario)
    {
        $request->getSession()->set($scenario, array(
            'to'            => '',
            'sms_code'      => '',
            'sms_last_time' => '',
            'sms_type'      => ''
        ));
    }

    public function loginWithToken()
    {
        $version = $this->request->query->get('version', 1);
        $mobile  = $this->controller->getSettingService()->get('mobile', array());
        if (empty($mobile['enabled'])) {
            return $this->createErrorResponse('client_closed', '没有搜索到该网校！');
        }

        $token = $this->controller->getUserToken($this->request);
        if ($token == null || $token['type'] != MobileBaseController::TOKEN_TYPE) {
            $token = null;
        }

        if (empty($token)) {
            $user = null;
        } else {
            $user = $this->controller->getUserService()->getUser($token['userId']);
        }

        $site = $this->controller->getSettingService()->get('site', array());

        if ($user != null) {
            if ($user['locked']) {
                return $this->createErrorResponse('user_locked', '用户已锁定，请联系网校管理员');
            }
            $userProfile = $this->controller->getUserService()->getUserProfile($token['userId']);
            $userProfile = $this->filterUserProfile($userProfile);
            $user        = array_merge($user, $userProfile);
        }

        $result = array(
            'token' => empty($token) ? '' : $token['token'],
            'user'  => empty($user) ? null : $this->controller->filterUser($user),
            'site'  => $this->getSiteInfo($this->request, $version)
        );

        $this->log("user_login", "用户二维码登录", array(
            "userToken" => $token)
        );

        $delTokens = $this->controller->getTokenService()->findTokensByUserIdAndType($token['userId'], $token['type']);
        if (empty($delTokens)) {
            return $result;
        }

        foreach ($delTokens as $delToken) {
            if ($delToken['token'] != $token['token']) {
                $this->controller->getTokenService()->destoryToken($delToken['token']);
            }
        }

        return $result;
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

        if ($user['locked']) {
            return $this->createErrorResponse('user_locked', '用户已锁定，请联系网校管理员');
        }
        $token = $this->controller->createToken($user, $this->request);

        $userProfile = $this->controller->getUserService()->getUserProfile($user['id']);
        $userProfile = $this->filterUserProfile($userProfile);
        $user        = array_merge($user, $userProfile);

        $this->controller->getLogService()->info(MobileBaseController::MOBILE_MODULE, "user_login", "用户登录", array(
            "username" => $username
        ));

        $result = array(
            'token' => $token,
            'user'  => $this->controller->filterUser($user)
        );

        $this->controller->getLogService()->info(MobileBaseController::MOBILE_MODULE, "user_login", "用户登录", array(
            "username" => $username
        ));

        $delTokens = $this->controller->getTokenService()->findTokensByUserIdAndType($user['id'], MobileBaseController::TOKEN_TYPE);
        if (empty($delTokens)) {
            return $result;
        }

        foreach ($delTokens as $delToken) {
            if ($delToken['token'] != $token) {
                $this->controller->getTokenService()->destoryToken($delToken['token']);
            }
        }

        return $result;
    }

    private function loadUserByUsername($request, $username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->controller->getUserService()->getUserByEmail($username);
        } elseif ($this->checkPhoneNum($username)) {
            $user = $this->controller->getUserService()->getUserByVerifiedMobile($username);
        } else {
            $user = $this->controller->getUserService()->getUserByNickname($username);
        }

        if (empty($user)) {
            return null;
        }
        $user['currentIp'] = $request->getClientIp();

        return $user;
    }

    public function getFollowings()
    {
        $userId = $this->getParam('userId');
        $start  = $this->getParam('start', 0);
        $limit  = $this->getParam('limit', 10);
        if (empty($userId)) {
            return $this->createErrorResponse('userId', "userId参数错误");
        }
        $followings = $this->controller->getUserService()->findUserFollowing($userId, $start, $limit);
        $followIds  = ArrayToolkit::column($followings, 'id');
        $result     = array();
        $index      = 0;
        foreach ($followIds as $followingId) {
            $user           = $this->controller->getUserService()->getUser($followingId);
            $userProfile    = $this->controller->getUserService()->getUserProfile($followingId);
            $userProfile    = $this->filterUserProfile($userProfile);
            $user           = array_merge($user, $userProfile);
            $result[$index] = $this->controller->filterUser($user);
            $index++;
        }
        return $result;
    }

    public function getFollowers()
    {
        $userId = $this->getParam('userId');
        $start  = $this->getParam('start', 0);
        $limit  = $this->getParam('limit', 10);
        if (empty($userId)) {
            return $this->createErrorResponse('userId', "userId参数错误");
        }
        $followers = $this->controller->getUserService()->findUserFollowers($userId, $start, $limit);
        $followIds = ArrayToolkit::column($followers, 'id');
        $index     = 0;
        foreach ($followIds as $followerId) {
            $user           = $this->controller->getUserService()->getUser($followerId);
            $userProfile    = $this->controller->getUserService()->getUserProfile($followerId);
            $userProfile    = $this->filterUserProfile($userProfile);
            $user           = array_merge($user, $userProfile);
            $result[$index] = $this->controller->filterUser($user);
            $index++;
        }
        return $result;
    }

    public function searchUserIsFollowed()
    {
        $userId       = $this->getParam('userId');
        $toId         = $this->getParam('toId');
        $followingIds = array($toId);
        $result       = $this->controller->getUserService()->filterFollowingIds($userId, $followingIds);

        if (!$result || empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    public function follow()
    {
        $user = $this->controller->getUserByToken($this->request);
        $toId = $this->getParam('toId');
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，无法获取信息数据");
        }

        try {
            $result = $this->controller->getUserService()->follow($user['id'], $toId);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        $message = array('userId' => $user['id'],
            'userName'                => $user['nickname'],
            'opration'                => 'follow');
        $this->controller->getNotificationService()->notify($toId, 'user-follow', $message);

        return $result;
    }

    public function unfollow()
    {
        $user = $this->controller->getUserByToken($this->request);
        $toId = $this->getParam('toId');
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，无法获取信息数据");
        }

        try {
            $result = $this->controller->getUserService()->unFollow($user['id'], $toId);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        $message = array('userId' => $user['id'],
            'userName'                => $user['nickname'],
            'opration'                => 'unfollow');
        $this->getNotificationService()->notify($toId, 'user-follow', $message);

        return $result;
    }

    public function getConversationIdByFromIdAndToId()
    {
        $fromId = $this->getParam('fromId');
        $toId   = $this->getParam('toId');
        $result = $this->getMessageService()->getConversationByFromIdAndToId($fromId, $toId);
        if (!empty($result)) {
            $fromUser               = $this->controller->getUserService()->getUser($fromId);
            $result['fromUserName'] = $fromUser['nickname'];
        }
        return $result;
    }

    public function getUserNum()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', "您尚未登录，无法获取信息数据");
        }

        $conditions = array(
            'userId' => $user['id'],
            'type'   => 'question'
        );
        $total   = $this->controller->getThreadService()->searchThreadCount($conditions);
        $threads = $this->controller->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            0,
            $total
        );
        $courses                 = $this->controller->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
        $threadSum               = $this->controller->getThreadService()->searchThreadCountInCourseIds($conditions);

        $conditions = array(
            'userId' => $user['id'],
            'type'   => 'discussion'
        );
        $totalDiscussion = $this->controller->getThreadService()->searchThreadCount($conditions);
        $discussion      = $this->controller->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            0,
            $totalDiscussion
        );

        $discussionCourses = $this->controller->getCourseService()->findCoursesByIds(ArrayToolkit::column($discussion, 'courseId'));

        $conditions['courseIds'] = ArrayToolkit::column($discussionCourses, 'id');

        $discussionSum = $this->controller->getThreadService()->searchThreadCountInCourseIds($conditions);

        $conditions = array(
            'userId'             => $user['id'],
            'noteNumGreaterThan' => 0.1
        );

        $total = $this->controller->getCourseService()->searchMemberCount($conditions);

        $courseMembers = $this->controller->getCourseService()->searchMember($conditions, 0, $total);

        $noteSum = 0;
        foreach ($courseMembers as $member) {
            $noteSum += $member['noteNum'];
        }

        settype($noteSum, "string");

        $testSum = $this->getTestpaperService()->findTestpaperResultsCountByUserId($user['id']);

        return array('thread' => $threadSum,
            'discussion'          => $discussionSum,
            'note'                => $noteSum,
            'test'                => $testSum);
    }

    public function getSchoolRoom()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $result = array(
                array('title' => '在学直播', 'data' => null),
                array('title' => '在学课程', 'data' => null),
                array('title' => '问答', 'data' => null),
                array('title' => '讨论', 'data' => null),
                array('title' => '笔记', 'data' => null),
                array('title' => '私信', 'data' => null));
        }
        $index          = 0;
        $dataLiveCourse = null;
        $liveCourse     = $this->controller->filterOneLiveCourseByDESC($user);
        if (sizeof($liveCourse) == 0) {
            $dataLiveCourse = null;
        } else {
            $liveCourse     = reset($liveCourse);
            $dataLiveCourse = array(
                'content'  => $liveCourse['title'],
                'id'       => $liveCourse['id'],
                'courseId' => $liveCourse['id'],
                'lessonId' => null,
                'time'     => $liveCourse['liveStartTime']
            );
        }
        $result[$index] = array(
            'title' => '在学直播',
            'data'  => $dataLiveCourse
        );
        $index++;

        $courseConditions = array(
            'userId' => $user['id']
        );
        $sort = array(
            'startTime',
            'DESC'
        );
        $allCourseTotal = $this->controller->getCourseService()->searchLearnCount($courseConditions);
        $allLearnCourse = $this->controller->getCourseService()->searchLearns($courseConditions, $sort, 0, $allCourseTotal);
        $courseInfo     = null;
        $resultCourse   = null;
        foreach ($allLearnCourse as $key => $value) {
            $courseInfo = $this->controller->getCourseService()->getCourse($allLearnCourse[$key]['courseId']);
            if ($courseInfo['type'] == 'live') {
                continue;
            } else {
                $resultCourse = $value;
                break;
            }
        }
        if ($courseInfo != null) {
            $courseInfo = $this->controller->getCourseService()->getCourse($resultCourse['courseId']);

            $data = array(
                'content'  => $courseInfo['title'],
                'id'       => $resultCourse['id'],
                'courseId' => $resultCourse['courseId'],
                'lessonId' => $courseInfo['largePicture'],
                'time'     => Date('c', $resultCourse['startTime'])
            );
        } else {
            $data = null;
        }
        $result[$index] = array(
            'title' => '在学课程',
            'data'  => $data
        );
        $index++;

        $learningCourseTotal = $this->controller->getCourseService()->findUserLeaningCourseCount($user['id']);
        $learningCourses     = $this->controller->getCourseService()->findUserLeaningCourses($user['id'], 0, $learningCourseTotal);
        $resultLearning      = $this->controller->filterCourses($learningCourses);

        $learnedCourseTotal = $this->controller->getCourseService()->findUserLeanedCourseCount($user['id']);
        $learnedCourses     = $this->controller->getCourseService()->findUserLeanedCourses($user['id'], 0, $learnedCourseTotal);
        $resultLearned      = $this->controller->filterCourses($learnedCourses);
        $courseIds          = ArrayToolkit::column($resultLearning + $resultLearned, 'id');

        $threadData     = null;
        $discussionData = null;
        if (sizeof($courseIds) > 0) {
            $conditions = array(
                'courseIds' => $courseIds,
                'type'      => 'question'
            );

            $resultThread = $this->controller->getThreadService()->searchThreadInCourseIds($conditions, 'posted', 0, 1);

            $resultThread = reset($resultThread);

            if ($resultThread != false) {
                $threadData = array(
                    'content'  => $resultThread['title'],
                    'id'       => $resultThread['id'],
                    'courseId' => $resultThread['courseId'],
                    'lessonId' => $resultThread['lessonId'],
                    'time'     => Date('c', $resultThread['latestPostTime'])
                );
            }

            $conditions['type'] = 'discussion';
            $resultDiscussion   = $this->controller->getThreadService()->searchThreadInCourseIds($conditions, 'posted', 0, 1);
            $resultDiscussion   = reset($resultDiscussion);

            if ($resultDiscussion != false) {
                $discussionData = array(
                    'content'  => $resultDiscussion['title'],
                    'id'       => $resultDiscussion['id'],
                    'courseId' => $resultDiscussion['courseId'],
                    'lessonId' => $resultDiscussion['lessonId'],
                    'time'     => Date('c', $resultDiscussion['latestPostTime'])
                );
            } else {
                $discussionData = null;
            }
        }

        $result[$index] = array(
            'title' => '问答',
            'data'  => $threadData
        );
        $index++;

        $result[$index] = array(
            'title' => '讨论',
            'data'  => $discussionData
        );
        $index++;
        $conditions = array(
            'userId'             => $user['id'],
            'noteNumGreaterThan' => 0
        );

        $updateTimeNote  = $this->controller->getNoteService()->searchNotes($conditions, array('updatedTime' => 'DESC'), 0, 1);
        $createdTimeNote = $this->controller->getNoteService()->searchNotes($conditions, array('createdTime' => 'DESC'), 0, 1);

        $lastestNote = array();
        if (sizeof($updateTimeNote) > 0 && sizeof($createdTimeNote) > 0) {
            if ($updateTimeNote[0]['updatedTime'] > $createdTimeNote[0]['createdTime']) {
                $lastestNote = $updateTimeNote;
            } else {
                $lastestNote = $createdTimeNote;
            }
        } elseif (sizeof($updateTimeNote) == 0 && sizeof($createdTimeNote) > 0) {
            $lastestNote = $createdTimeNote;
        } elseif (sizeof($updateTimeNote) > 0 && sizeof($createdTimeNote) == 0) {
            $lastestNote = $updateTimeNote;
        }

        $lastestNote = reset($lastestNote);
        if ($lastestNote != false) {
            $data = array(
                'content'  => $lastestNote['content'],
                'id'       => $lastestNote['id'],
                'courseId' => $lastestNote['courseId'],
                'lessonId' => $lastestNote['lessonId']
            );
            if ($lastestNote['updatedTime'] > $lastestNote['createdTime']) {
                $data['time'] = Date('c', $lastestNote['updatedTime']);
            } else {
                $data['time'] = Date('c', $lastestNote['createdTime']);
            }
        } else {
            $data = null;
        }
        $result[$index] = array(
            'title' => '笔记',
            'data'  => $data
        );
        $index++;

        $messageConditions = array(
            'toId' => $user['id']
        );
        $sort = array();

        $msgCount      = $this->getMessageService()->getUserConversationCount($user['id']);
        $conversations = $this->getMessageService()->findUserConversations($user['id'], 0, $msgCount);
        foreach ($conversations as $key => $value) {
            $sort[$key] = $value['latestMessageTime'];
        }

        if ($conversations != null) {
            array_multisort($sort, SORT_DESC, $conversations);
        }

        $lastestMessage = reset($conversations);
        if ($lastestMessage != false) {
            $data = array(
                'content'  => $lastestMessage['latestMessageContent'],
                'id'       => $lastestMessage['id'],
                'courseId' => $lastestMessage['fromId'],
                'lessonId' => $lastestMessage['toId'],
                'time'     => Date('c', $lastestMessage['createdTime'])
            );
        } else {
            $data = null;
        }
        $result[$index] = array(
            'title' => '私信',
            'data'  => $data
        );
        $index++;

        return $result;
    }

    public function getCourseTeachers()
    {
        $courseId = $this->getParam("courseId");
        $course   = $this->controller->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return $this->createErrorResponse('not_found', "课程不存在");
        }

        $users = $this->controller->getUserService()->findUsersByIds($course['teacherIds']);

        return array_values($this->filterUsersFiled($users));
    }

    protected function getAuthService()
    {
        return $this->controller->getService('User.AuthService');
    }
}
