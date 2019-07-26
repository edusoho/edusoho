<?php

namespace ApiBundle\Api\Resource\Login;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DeviceToolkit;
use AppBundle\Common\MathToolkit;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\Sms\SmsException;
use Biz\System\Service\LogService;
use Biz\User\Service\AuthService;
use Biz\User\Service\BatchNotificationService;
use Biz\User\Service\TokenService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;

class Login extends AbstractResource
{
    private $supportLoginType = array(
        'sms',
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $loginType = $request->request->get('loginType', '');

        if (!in_array($loginType, $this->supportLoginType)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        $method = "loginBy${loginType}";

        return $this->$method($request);
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function loginBySms(ApiRequest $request)
    {
        $fields = $request->request->all();

        if (!ArrayToolkit::requireds($fields, array(
            'mobile',
            'smsToken',
            'smsCode',
        ))) {
            throw CommonException::ERROR_PARAMETER();
        }

        // 检查短信验证码
        $status = $this->getBizSms()->check(BizSms::SMS_LOGIN, $fields['mobile'], $fields['smsToken'], $fields['smsCode']);
        if (BizSms::STATUS_SUCCESS != $status) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        // 按手机号获取用户，没有就注册
        $user = $this->getUserService()->getUserByVerifiedMobile($fields['mobile']);
        if (empty($user)) {
            $user = $this->createUser($request, $fields);
        }
        $this->appendUser($user);

        $token = $this->getLoginToken($user['id'], $fields['smsCode'], $fields['smsToken'], $fields['mobile']);

        // 登录后获取通知
        $this->getBatchNotificationService()->checkoutBatchNotification($user['id']);
        // 积分插件：在登录后加积分
        $this->getDispatcher()->dispatch('user.login', new Event($user));

        $this->getLogService()->info('sms', 'login', "用户{$user['nickname']}通过短信快捷登录登录成功", array('userId' => $user['id']));

        return array(
            'token' => $token,
            'user' => $user,
        );
    }

    private function createUser(ApiRequest $request, $fields)
    {
        $nickname = substr(MathToolkit::uniqid(), 8, 16);
        while (!$this->getUserService()->isNicknameAvaliable($nickname)) {
            $nickname = MathToolkit::uniqid();
        }

        $registeredWay = DeviceToolkit::getMobileDeviceType($request->headers->get('user-agent'));
        $newUser = array(
            'mobile' => $fields['mobile'],
            'emailOrMobile' => $fields['mobile'],
            'nickname' => $nickname,
            'password' => substr($fields['mobile'], mt_rand(0, 4), 6),
            'registeredWay' => $registeredWay,
            'createdIp' => $request->getHttpRequest()->getClientIp(),
        );

        $user = $this->getAuthService()->register($newUser);

        $this->getLogService()->info('sms', 'login', "用户{$user['nickname']}通过短信快捷登录注册成功", array('userId' => $user['id']));

        return $user;
    }

    private function appendUser(&$user)
    {
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($profile, $user);

        if ($this->isPluginInstalled('vip')) {
            $vip = $this->service('VipPlugin:Vip:VipService')->getMemberByUserId($user['id']);
            $level = $this->service('VipPlugin:Vip:LevelService')->getLevel($vip['levelId']);
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
        $user['following'] = (string) $this->getUserService()->findUserFollowingCount($user['id']);
        $user['follower'] = (string) $this->getUserService()->findUserFollowerCount($user['id']);

        return $user;
    }

    private function getLoginToken($userId, $smsCode, $smsToken, $mobile)
    {
        $token = $this->getTokenService()->makeToken('mobile_login', array(
            'times' => 0,
            'duration' => 60 * 60 * 24 * 7,
            'userId' => $userId,
            'data' => array(
                'sms_code' => $smsCode,
                'sms_token' => $smsToken,
                'mobile' => $mobile,
            ),
        ));

        return $token['token'];
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
