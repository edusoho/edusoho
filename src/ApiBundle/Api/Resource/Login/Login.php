<?php

namespace ApiBundle\Api\Resource\Login;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Sms\SmsException;
use Biz\Sms\SmsType;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\CurrentUser;
use Biz\User\Service\AuthService;
use Biz\User\Service\BatchNotificationService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class Login extends AbstractResource
{
    private $supportLoginTypes = array(
        'sms',
    );

    private $supportClients = array(
        'app', 'miniprogram', 'h5'
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $loginType = $request->request->get('loginType', '');

        if (!in_array($loginType, $this->supportLoginTypes)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        $method = "loginBy${loginType}";

        return $this->$method($request);
    }

    public function loginBySms(ApiRequest $request)
    {
        $fields = $request->request->all();

        if (!ArrayToolkit::requireds($fields, array('mobile', 'smsToken', 'smsCode', 'client'))) {
            throw CommonException::ERROR_PARAMETER();
        }

        $client = $this->getClient($fields['client'], $request->headers->get('User-Agent'));
        $clientIp = $request->getHttpRequest()->getClientIp();
        $mobile = $fields['mobile'];

        // 检查短信验证码
        $status = $this->getBizSms()->check(BizSms::SMS_LOGIN, $mobile, $fields['smsToken'], $fields['smsCode']);
        if (BizSms::STATUS_SUCCESS != $status) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        // 按手机号获取用户，没有就注册
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (empty($user)) {
            $this->checkMobileRegisterSetting();
            $user = $this->createUser($clientIp, $client, $mobile);
            $this->sendRegisterSms($mobile, $user['id'], $user['nickname'], $user['realPassword']);
            unset($user['realPassword']);
        }
        $this->appendUser($user);

        $token = $this->getLoginToken($user['id'], $fields['smsCode'], $fields['smsToken'], $mobile, $client);

        $this->afterLogin($user, $token, $client, $clientIp);

        $this->getLogService()->info('sms', 'sms_login', "用户{$user['nickname']}通过短信快捷登录登录成功", array('userId' => $user['id']));

        return array(
            'token' => $token,
            'user' => $user,
        );
    }

    private function createUser($clientIp, $client, $mobile)
    {
        $nickname = substr(MathToolkit::uniqid(), 8, 16);
        while (!$this->getUserService()->isNicknameAvaliable($nickname)) {
            $nickname = MathToolkit::uniqid();
        }
        $password = substr($mobile, mt_rand(0, 4), 6);

        $newUser = array(
            'mobile' => $mobile,
            'emailOrMobile' => $mobile,
            'nickname' => $nickname,
            'password' => $password,
            'registeredWay' => $client,
            'createdIp' => $clientIp,
        );

        $user = $this->getAuthService()->register($newUser);
        $user['realPassword'] = $password;

        $this->getLogService()->info('sms', 'sms_register', "用户{$user['nickname']}通过短信快捷登录注册成功", array('userId' => $user['id']));

        return $user;
    }

    private function sendRegisterSms($mobile, $userId, $nickname, $password)
    {
        $site = $this->getSettingService()->get('site', array());

        $templateParams = array(
            'nickname' => $nickname,
            'url' => $site['url'],
            'password' => $password,
        );

        $smsParams = array(
            'mobiles' => $mobile,
            'templateId' => SmsType::IMPORT_USER,
            'templateParams' => $templateParams,
        );

        try {
            $this->getSDKSmsService()->sendToOne($smsParams);
            $this->getLogService()->info('sms', 'send_register', "管理员给用户 {$nickname}($userId) 发送账号信息短信");
        } catch (\Exception $e) {
            $this->getLogService()->error('sms', 'send_register', "管理员给用户 {$nickname}({$userId}) 发送账号信息短信失败：" . $e->getMessage());
            throw $e;
        }

    }

    private function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->getVipService()->getMemberByUserId($user['id']);
            $level = $this->getLevelService()->getLevel($vip['levelId']);
            if ($vip) {
                $user['vip'] = array(
                    'levelId' => $vip['levelId'],
                    'vipName' => $level['name'],
                    'deadline' => date('c', $vip['deadline']),
                    'seq' => $level['seq'],
                );
            } else {
                $user['vip'] = null;
            }
        }
        $user['following'] = $this->getUserService()->findUserFollowingCount($user['id']);
        $user['follower'] = $this->getUserService()->findUserFollowerCount($user['id']);
    }



    private function getLoginToken($userId, $smsCode, $smsToken, $mobile, $client)
    {
        $token = $this->getTokenService()->makeToken('mobile_login', array(
            'times' => 0,
            'duration' => TimeMachine::ONE_WEEK,
            'userId' => $userId,
            'data' => array(
                'sms_code' => $smsCode,
                'sms_token' => $smsToken,
                'client' => $client,
                'mobile' => $mobile,
            ),
        ));

        return $token['token'];
    }

    private function checkMobileRegisterSetting()
    {
        if (!$this->getUserService()->isMobileRegisterMode()) {
            throw SettingException::FORBIDDEN_SMS_SEND();
        }
    }

    private function getClient($client, $userAgent)
    {
        if (!in_array($client, $this->supportClients)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $client == 'app' ? DeviceToolkit::getMobileDeviceType($userAgent) : $client;
    }

    private function afterLogin($user, $newToken, $client, $clientIp)
    {
        // 记录登录时间
        $this->setCurrentUser($user, $clientIp);
        $this->getUserService()->markLoginInfo($client);

        // 登录后获取通知
        $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);

        // 积分插件：在登录后加积分
        $this->getDispatcher()->dispatch('user.login', new Event($user));

        // APP侧需要删除token，以达到同一时间只有一个设备在线的要求
        $this->deleteTokens($user, $newToken);
    }

    private function deleteTokens($user, $newToken)
    {
        $delTokens = $this->getTokenService()->findTokensByUserIdAndType($user['id'], 'mobile_login');
        if (empty($delTokens)) {
            return;
        }

        foreach ($delTokens as $delToken) {
            if ($delToken['token'] != $newToken) {
                $this->getTokenService()->destoryToken($delToken['token']);
            }
        }
    }

    private function setCurrentUser($user, $clientIp)
    {
        $allowUser = array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'currentIp' => $clientIp,
            'roles' => $user['roles'],
            'type' => $user['type']
        );
        $currentUser = new CurrentUser();
        $currentUser = $currentUser->fromArray($allowUser);
        $biz = $this->getBiz();
        $biz['user'] = $currentUser;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return LevelService
     */
    private function getLevelService()
    {
        return $this->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    private function getVipService()
    {
        return $this->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return BatchNotificationService
     */
    private function getBatchNotificationService()
    {
        return $this->service('User:BatchNotificationService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    /**
     * @return TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    /**
     * @return AuthService
     */
    private function getAuthService()
    {
        return $this->service('User:AuthService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    protected function getSDKSmsService()
    {
        return $this->biz['qiQiuYunSdk.sms'];
    }

    private function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }

    private function getDispatcher()
    {
        $biz = $this->getBiz();

        return $biz['dispatcher'];
    }
}