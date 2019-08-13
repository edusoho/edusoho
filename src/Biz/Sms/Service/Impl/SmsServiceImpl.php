<?php

namespace Biz\Sms\Service\Impl;

use Biz\BaseService;
use Biz\Sms\Service\SmsService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\System\SettingException;
use Biz\Sms\SmsException;
use Biz\User\UserException;

class SmsServiceImpl extends BaseService implements SmsService
{
    protected $apiFactory;

    public function isOpen($smsType)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');

        if ((isset($cloudSmsSetting['sms_enabled'])) && ('1' == $cloudSmsSetting['sms_enabled'])
            && (isset($cloudSmsSetting[$smsType])) && ('on' == $cloudSmsSetting[$smsType])) {
            return true;
        }

        return false;
    }

    public function smsSend($smsType, $userIds, $description, $parameters = array())
    {
        if (!$this->isOpen($smsType)) {
            $this->createNewException(SmsException::FORBIDDEN_SMS_SETTING());
        }

        //这里不考虑去重，只考虑unlock
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to = implode(',', $mobiles);
        try {
            $api = $this->createCloudeApi();
            $result = $api->post('/sms/send', array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters));
        } catch (\RuntimeException $e) {
            $this->createNewException(SmsException::FAILED_SEND());
        }

        $message = sprintf('对%s发送用于%s的通知短信', $to, $smsType);
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
    }

    public function sendVerifySms($smsType, $to, $smsLastTime = 0)
    {
        if (!$this->checkPhoneNum($to)) {
            $this->createNewException(SmsException::ERROR_MOBILE());
        }

        if (!$this->isOpen($smsType)) {
            $this->createNewException(SmsException::FORBIDDEN_SMS_SETTING());
        }

        $allowedTime = 120;
        $currentTime = time();
        if ($this->isNeedWaiting($smsLastTime, $currentTime, $allowedTime)) {
            $this->createNewException(SmsException::NEED_WAIT());
        }
        $currentUser = $this->getCurrentUser();

        $this->checkSmsType($smsType, $currentUser);

        if (in_array($smsType, array('sms_bind', 'sms_registration'))) {
            if ('sms_bind' == $smsType) {
                $description = '手机绑定';
            } else {
                $description = '用户注册';
            }

            if (!$this->getUserService()->isMobileUnique($to)) {
                $this->createNewException(UserException::ERROR_MOBILE_REGISTERED());
            }
        }

        if ('sms_forget_password' == $smsType) {
            $description = '登录密码重置';
            $targetUser = $this->getUserService()->getUserByVerifiedMobile($to);

            if (empty($targetUser)) {
                $this->createNewException(UserException::NOTFOUND_USER());
            }

            if ((!isset($targetUser['verifiedMobile']) || (0 == strlen($targetUser['verifiedMobile'])))) {
                $this->createNewException(SmsException::NOTFOUND_BIND_MOBILE());
            }

            if ($targetUser['verifiedMobile'] != $to) {
                $this->createNewException(SmsException::ERROR_MATCH_MOBILE_USERNAME());
            }
            $to = $targetUser['verifiedMobile'];
        }

        if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {
            if ('sms_user_pay' == $smsType) {
                $description = '网站余额支付';
            } else {
                $description = '支付密码重置';
            }

            if ((!isset($currentUser['verifiedMobile']) || (0 == strlen($currentUser['verifiedMobile'])))) {
                $this->createNewException(SmsException::NOTFOUND_BIND_MOBILE());
            }

            if ($currentUser['verifiedMobile'] != $to) {
                $this->createNewException(SmsException::ERROR_MOBILE());
            }

            $to = $currentUser['verifiedMobile'];
        }

        if ('system_remind' == $smsType) {
            $description = '直播公开课';
        }

        if ('sms_login' == $smsType) {
            $description = '手机快捷登录';
            // FIXME 先兼容教育云，待教育云添加新的类型
            $smsType = 'sms_bind';
        }

        $smsCode = $this->generateSmsCode();

        try {
            $api = $this->createCloudeApi();
            $result = $api->post("/sms/{$api->getAccessKey()}/sendVerify", array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'verify' => $smsCode));
            if (isset($result['error'])) {
                return array('error' => sprintf('发送失败, %s', $result['error']));
            }
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            return array('error' => sprintf('发送失败, %s', $message));
        }

        $result['to'] = $to;
        $result['smsCode'] = $smsCode;
        $result['userId'] = $currentUser['id'];

        if (0 != $currentUser['id']) {
            $result['nickname'] = $currentUser['nickname'];
        }

        $this->getLogService()->info(
            'sms', $smsType, sprintf('userId:%s,对%s发送用于%s的验证短信%s', $currentUser['id'], $to, $smsType, $smsCode), $result);

        return array(
            'to' => $to,
            'captcha_code' => $smsCode,
            'sms_last_time' => $currentTime,
        );
    }

    public function checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode)
    {
        if (0 == strlen($actualSmsCode) || 0 == strlen($expectedSmsCode)) {
            return array('success' => false, 'message' => '验证码错误');
        }

        if ('' != $actualMobile && !empty($expectedMobile) && $actualMobile != $expectedMobile) {
            return array('success' => false, 'message' => '验证码和手机号码不匹配');
        }

        if ($expectedSmsCode == $actualSmsCode) {
            $result = array('success' => true, 'message' => '验证码正确');
        } else {
            $result = array('success' => false, 'message' => '验证码错误');
        }

        return $result;
    }

    protected function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password', 'system_remind', 'sms_login'))) {
            $this->createNewException(SmsException::ERROR_SMS_TYPE());
        }

        $smsSetting = $this->getSettingService()->get('cloud_sms', array());

        if (!empty($smsSetting["{$smsType}"]) && 'on' != $smsSetting["{$smsType}"] && !$this->getUserService()->isMobileRegisterMode()) {
            $this->createNewException(SettingException::FORBIDDEN_MOBILE_REGISTER());
        }
    }

    protected function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);

        for ($i = 1; $i < $length; ++$i) {
            $code = $code.rand(0, 9);
        }

        return $code;
    }

    protected function checkPhoneNum($num)
    {
        return preg_match("/^1\d{10}$/", $num);
    }

    protected function isNeedWaiting($smsLastTime, $currentTime, $allowedTime = 120)
    {
        if (0 == strlen($smsLastTime)) {
            return false;
        }
        if (($currentTime - $smsLastTime) < $allowedTime) {
            return true;
        }

        return false;
    }

    protected function createCloudeApi()
    {
        if (!$this->apiFactory) {
            $this->apiFactory = CloudAPIFactory::create('leaf');
        }

        return $this->apiFactory;
    }

    /**
     * 仅给单元测试mock用。
     */
    public function setCloudeApi($cloudeApi)
    {
        $this->apiFactory = $cloudeApi;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
