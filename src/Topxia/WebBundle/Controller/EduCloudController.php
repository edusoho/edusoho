<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    public function smsSendAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            if ($this->getCloudSmsKey('sms_enabled') != '1') {
                return $this->createJsonResponse(array('error' => 'sms is disabled'));
            }

            $currentUser = $this->getCurrentUser();
            $currentTime = time();

            $smsLastTime = $request->getSession()->get('sms_last_time');
            if (!$this->checkLastTime($smsLastTime)) {
                return $this->createJsonResponse(array('error' => 'wait to resent'));
            }

            $smsType = $request->request->get('sms_type');
            $this->checkSmsType($smsType, $currentUser);

            if ($smsType == 'sms_registration') {
                $to = $request->request->get('to');
            }

            if ($smsType == 'sms_forget_password') {
                $nickname = $request->request->get('nickname');
                if (strlen($nickname) == 0){
                    return $this->createJsonResponse(array('error' => 'nickname is null'));
                }
                $targetUser = $this->getUserService()->getUserByNickname($nickname);
                if (empty($targetUser)){
                    return $this->createJsonResponse(array('error' => 'user not exist'));    
                }
                if ((!isset($targetUser['verifiedMobile']) || (strlen($targetUser['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有verifiedMobile'));
                }
                $to = $targetUser['verifiedMobile'];
            }

            if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {
                $user = $currentUser->toArray();
                if ((!isset($user['verifiedMobile']) || (strlen($user['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有verifiedMobile'));
                }
                $to = $user['verifiedMobile'];
            }

            if (!$this->checkPhoneNum($to)){
                return $this->createJsonResponse(array('error' => "mobile number error:{$to}"));
            }

            $smsCode = $this->generateSmsCode();
            try {
                $result = $this->getEduCloudService()->sendSms($to, $smsCode);
                if (isset($result['error'])) {
                    $serializedResult = serialize($result);
                    return $this->createJsonResponse(array('error' => "failed to send sms|{$serializedResult}"));
                }
            } catch (\RuntimeException $e) {
                $serializedResult = serialize($result);
                return $this->createJsonResponse(array('error' => "failed to send sms|{$serializedResult}"));
            }

            $this->getLogService()->info('sms', 'sms', "对{$to}发送用于{$smsType}的验证短信{$smsCode}", $result);

            $request->getSession()->set('sms_code', $smsCode);
            $request->getSession()->set('sms_last_time', $currentTime);
            $request->getSession()->set('sms_type', $smsType);

            return $this->createJsonResponse(array('ACK' => 'ok'));
        }

        return $this->createJsonResponse(array('error' => 'GET method'));
    }

    private function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);
        for ($i = 1; $i < $length; $i++) {
            $code = $code . rand(0, 9);
        }
        return $code;
    }

    private function checkPhoneNum($num)
    {
        return preg_match("/^1\d{10}$/", $num);
    }

    private function checkLastTime($smsLastTime, $allowedTime = 120)
    {
        if (!((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime))) {
            return false;
        }
        return true;
    }

    private function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password'))) {
            throw new \RuntimeException('不存在的sms Type');
        }

        if ((!$user->isLogin()) && (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password')))) {
            throw new \RuntimeException('用户未登陆');
        }

        if ($this->getCloudSmsKey($smsType) != 'on') {
            throw new \RuntimeException('该使用场景未开启');
        }
    }

    private function getCloudSmsKey($key)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        return $setting[$key];
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
