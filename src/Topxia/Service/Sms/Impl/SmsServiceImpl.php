<?php
namespace Topxia\Service\Sms\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Sms\SmsService;
use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\User\UserService;

class SmsServiceImpl extends BaseService implements SmsService
{
    public function isOpen($smsType)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');

        if ((isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1')
            && (isset($cloudSmsSetting[$smsType])) && ($cloudSmsSetting[$smsType] == 'on')) {
            return true;
        }

        return false;
    }

    public function smsSend($smsType, $userIds, $description, $parameters = array())
    {
        if (!$this->isOpen($smsType)) {
            throw new \RuntimeException($this->getKernel()->trans('云短信相关设置未开启!'));
        }

        //这里不考虑去重，只考虑unlock
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to      = implode(',', $mobiles);
        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->post("/sms/send", array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'parameters' => $parameters));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($this->getKernel()->trans('发送失败！'));
        }

        $message = $this->getKernel()->trans('对%To%发送用于%Type%的通知短信',array('%To%'=>$to ,'%Type%'=>$smsType));
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
    }

    public function sendVerifySms($smsType,$to,$smsLastTime = 0)
    {
        if (!$this->checkPhoneNum($to)) {
            throw new \RuntimeException($this->getKernel()->trans('手机号错误:%to%', array('%to%' => $to)));
        }

        if (!$this->isOpen($smsType)) {
            throw new \RuntimeException($this->getKernel()->trans('云短信相关设置未开启!'));
        }

        $allowedTime   = 120;
        $currentTime = time();
        if ($this->isNeedWaiting($smsLastTime, $currentTime, $allowedTime)) {
           throw new \RuntimeException($this->getKernel()->trans('请等待120秒再申请!'));
        }
        $currentUser = $this->getCurrentUser();

        $this->checkSmsType($smsType, $currentUser);

        if (in_array($smsType, array('sms_bind', 'sms_registration'))) {
            if ($smsType == 'sms_bind') {
                $description = $this->trans('手机绑定');
            } else {
                $description = $this->trans('用户注册');
            }

            $hasVerifiedMobile = (isset($currentUser['verifiedMobile']) && (strlen($currentUser['verifiedMobile']) > 0));

            if ($hasVerifiedMobile && ($to == $currentUser['verifiedMobile'])) {
                throw new \RuntimeException($this->getKernel()->trans('您已经绑定了该手机号码'));
            }

            if (!$this->getUserService()->isMobileUnique($to)) {
                throw new \RuntimeException($this->getKernel()->trans('该手机号码已被其他用户绑定'));
            }
        }

        if ($smsType == 'sms_forget_password') {
            $description = $this->trans('登录密码重置');
            $targetUser  = $this->getUserService()->getUserByVerifiedMobile($to);

            if (empty($targetUser)) {
                throw new \RuntimeException($this->getKernel()->trans('用户不存在'));
            }

            if ((!isset($targetUser['verifiedMobile']) || (strlen($targetUser['verifiedMobile']) == 0))) {
                throw new \RuntimeExceptionarray($this->getKernel()->trans('用户没有被绑定的手机号'));
            }

            if ($targetUser['verifiedMobile'] != $to) {
                throw new \RuntimeExceptionarray($this->getKernel()->trans('手机与用户名不匹配'));
            }
            $to = $targetUser['verifiedMobile'];
        }

        if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {

                if ($smsType == 'sms_user_pay') {
                    $description = $this->trans('网站余额支付');
                } else {
                    $description = $this->trans('支付密码重置');
                }

                if ((!isset($currentUser['verifiedMobile']) || (strlen($currentUser['verifiedMobile']) == 0))) {
                    throw new \RuntimeExceptionarray($this->getKernel()->trans('用户没有被绑定的手机号'));
                }

                if ($currentUser['verifiedMobile'] != $to) {
                    throw new \RuntimeExceptionarray($this->getKernel()->trans('您输入的手机号，不是已绑定的手机'));
                }

                $to = $currentUser['verifiedMobile'];
            }

            if ($smsType == 'system_remind') {
                $to          = $to;
                $description = '直播公开课';
            }

         

            $smsCode = $this->generateSmsCode();

            try {
                $api    = CloudAPIFactory::create('leaf');
                $result = $api->post("/sms/{$api->getAccessKey()}/sendVerify", array('mobile' => $to, 'category' => $smsType, 'sendStyle' => 'templateId', 'description' => $description, 'verify' => $smsCode));
                if (isset($result['error'])) {
                    return array('error' => $this->getKernel()->trans('发送失败, %resulterror%', array('%resulterror%' => $result['error'])));
                }
            } catch (\RuntimeException $e) {
                $message = $e->getMessage();
                return array('error' => $this->getKernel()->trans('发送失败, %message%',array('%message%' => $message)));
            }

            $result['to']      = $to;
            $result['smsCode'] = $smsCode;
            $result['userId']  = $currentUser['id'];

            if ($currentUser['id'] != 0) {
                $result['nickname'] = $currentUser['nickname'];
            }

            $this->getLogService()->info('sms', $smsType, $this->trans('userId:%currentUserid%,对%to%发送用于%smsType%的验证短信%smsCode%', array('%currentUserid%' => $currentUser['id'], '%to%' => $to, '%smsType%' => $smsType, '%smsCode%' => $smsCode)), $result);

            return array(
                'to'            => $to,
                'captcha_code'      => $smsCode,
                'sms_last_time' => $currentTime
            );
    }

    public function checkVerifySms($actualMobile, $expectedMobile ,$actualSmsCode, $expectedSmsCode)
    {
        if (strlen($actualSmsCode) == 0 || strlen($expectedSmsCode) == 0) {
            $result = array('success' => false, 'message' => $this->trans('验证码错误'));
        }

        if ($actualMobile != '' && !empty($expectedMobile) && $actualMobile != $expectedMobile) {
            return array('success' => false, 'message' => '验证码和手机号码不匹配');
        }

        if ($expectedSmsCode == $actualSmsCode) {
            $result = array('success' => true, 'message' => $this->trans('验证码正确'));
        } else {
            $result = array('success' => false, 'message' => $this->trans('验证码错误'));
        }

        return $result;
    }

    protected function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_bind', 'sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password', 'system_remind'))) {
            throw new \RuntimeException($this->trans('不存在的sms Type'));
        }

        if ($this->setting("cloud_sms.{$smsType}") != 'on' && !$this->getUserService()->isMobileRegisterMode()) {
            throw new \RuntimeException($this->trans('该使用场景未开启'));
        }
    }

    protected function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);

        for ($i = 1; $i < $length; $i++) {
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
        if (strlen($smsLastTime) == 0) {
            return false;
        }
        if (($currentTime - $smsLastTime) < $allowedTime) {
           return true;
        }
        return false;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}
