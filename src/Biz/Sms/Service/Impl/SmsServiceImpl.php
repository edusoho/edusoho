<?php

namespace Biz\Sms\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\Service\SmsService;
use Biz\Sms\SmsException;
use Biz\Sms\SmsScenes;
use Biz\Sms\SmsType;
use Biz\System\SettingException;
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

    public function smsSend($smsType, $userIds, $templateId, $parameters = [])
    {
        if (!$this->isOpen($smsType)) {
            $this->createNewException(SmsException::FORBIDDEN_SMS_SETTING());
        }

        //这里不考虑去重，只考虑unlock
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to = implode(',', $mobiles);
        try {
            $smsParams = [
                'mobiles' => $to,
                'templateId' => $templateId,
                'templateParams' => $parameters,
                'tag' => $this->matchSmsType($smsType),
            ];

            $this->getSDKSmsService()->sendToMany($smsParams);
        } catch (\RuntimeException $e) {
            $this->createNewException(SmsException::FAILED_SEND());
        }

        $message = sprintf('对%s发送用于%s的通知短信', $to, $smsType);
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
    }

    public function sendVerifySms($smsType, $to, $smsLastTime = 0, $unique = 1)
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

        if (in_array($smsType, ['sms_bind', 'sms_registration'])) {
            if (!$this->getUserService()->isMobileUnique($to) && $unique) {
                $this->createNewException(UserException::ERROR_MOBILE_REGISTERED());
            }
        }

        if ('sms_forget_password' == $smsType) {
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

        if (in_array($smsType, ['sms_user_pay', 'sms_forget_pay_password'])) {
            if ((!isset($currentUser['verifiedMobile']) || (0 == strlen($currentUser['verifiedMobile'])))) {
                $this->createNewException(SmsException::NOTFOUND_BIND_MOBILE());
            }

            if ($currentUser['verifiedMobile'] != $to) {
                $this->createNewException(SmsException::ERROR_MOBILE());
            }

            $to = $currentUser['verifiedMobile'];
        }

        if ('sms_login' == $smsType) {
            // FIXME 先兼容教育云，待教育云添加新的类型
            $smsType = 'sms_bind';
        }

        $smsCode = $this->generateSmsCode();

        try {
            $smsParams = [
                'mobiles' => $to,
                'templateId' => SmsType::VERIFY_CODE,
                'templateParams' => ['verify' => $smsCode],
                'tag' => $this->matchSmsType($smsType),
            ];

            $this->getSDKSmsService()->sendToOne($smsParams);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();

            return ['error' => sprintf('发送失败, %s', $message)];
        }

        $result = [
            'to' => $to,
            'smsCode' => $smsCode,
            'userId' => $currentUser['id'],
        ];

        if (0 != $currentUser['id']) {
            $result['nickname'] = $currentUser['nickname'];
        }

        $this->getLogService()->info(
            'sms', $smsType, sprintf('userId:%s,对%s发送用于%s的验证短信%s', $currentUser['id'], $to, $smsType, $smsCode), $result);

        return [
            'to' => $to,
            'captcha_code' => $smsCode,
            'sms_last_time' => $currentTime,
        ];
    }

    public function checkVerifySms($actualMobile, $expectedMobile, $actualSmsCode, $expectedSmsCode)
    {
        if (0 == strlen($actualSmsCode) || 0 == strlen($expectedSmsCode)) {
            return ['success' => false, 'message' => '验证码错误'];
        }

        if ('' != $actualMobile && !empty($expectedMobile) && $actualMobile != $expectedMobile) {
            return ['success' => false, 'message' => '验证码和手机号码不匹配'];
        }

        if ($expectedSmsCode == $actualSmsCode) {
            $result = ['success' => true, 'message' => '验证码正确'];
        } else {
            $result = ['success' => false, 'message' => '验证码错误'];
        }

        return $result;
    }

    protected function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, ['sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password', 'system_remind', 'sms_login'])) {
            $this->createNewException(SmsException::ERROR_SMS_TYPE());
        }

        $smsSetting = $this->getSettingService()->get('cloud_sms', []);

        if (!empty($smsSetting["{$smsType}"]) && 'on' != $smsSetting["{$smsType}"] && !$this->getUserService()->isMobileRegisterMode()) {
            $this->createNewException(SettingException::FORBIDDEN_MOBILE_REGISTER());
        }
    }

    protected function matchSmsType($smsType)
    {
        switch ($smsType) {
            case 'sms_bind':
                $smsTag = SmsScenes::MOBILE_PHONE_BINDING;
                break;
            case 'sms_user_pay':
                $smsTag = SmsScenes::USER_PAY;
                break;
            case 'sms_registration':
                $smsTag = SmsScenes::USER_REGISTRATION;
                break;
            case 'sms_forget_password':
                $smsTag = SmsScenes::LOGIN_PASSWORD_RESET;
                break;
            case 'sms_forget_pay_password':
                $smsTag = SmsScenes::PAYMENT_PASSWORD_RESER;
                break;
            case 'system_remind':
                $smsTag = SmsScenes::SYSTEM_REMIND;
                break;
            case 'sms_login':
                $smsTag = SmsScenes::USER_LOGIN;
                break;
            case 'sms_testpaper_check':
                $smsTag = SmsScenes::TESTPAPER_MARKED;
                break;
             case 'sms_homework_check':
                 $smsTag = SmsScenes::ASSIGNMENT_MARKED;
                 break;
            case 'sms_course_buy_notify':
                $smsTag = SmsScenes::COURSE_PURCHASE_RECEIPT;
                break;
            case 'sms_classroom_buy_notify':
                $smsTag = SmsScenes::CLASS_PURCHASE_RECEIPT;
                break;
            case 'sms_vip_buy_notify':
                $smsTag = SmsScenes::VIP_PURCHASE_RECEIPT;
                break;
            case 'sms_classroom_publish':
                $smsTag = SmsScenes::NEW_CLASS_RELEASE;
                break;
            case 'sms_course_publish':
                $smsTag = SmsScenes::NEW_COURSE_RELEASE;
                break;
            case 'sms_normal_lesson_publish':
                $smsTag = SmsScenes::COURSE_TASK_RELEASE;
                break;
            case 'sms_live_lesson_publish':
                $smsTag = SmsScenes::LIVE_TASK_RELEASE;
                break;
            case 'sms_coin_buy_notify':
                $smsTag = SmsScenes::VIRTUAL_COIN_RECEIPT;
                break;
            default:
                $smsTag = '';
                break;
        }

        return $smsTag;
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

    private function getSDKSmsService()
    {
        return $this->biz['ESCloudSdk.sms'];
    }
}
