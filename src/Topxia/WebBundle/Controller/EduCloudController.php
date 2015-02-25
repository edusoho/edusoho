<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EduCloudController extends BaseController
{
    private $debug = false;

    public function smsSendAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            if ($this->getCloudSmsKey('sms_enabled') != '1') {
                return $this->createJsonResponse(array('error' => '短信服务被管理员关闭了'));
            }

            $currentUser = $this->getCurrentUser();
            $currentTime = time();

            $smsLastTime = $request->getSession()->get('sms_last_time');

            if ($this->debug){
                $allowedTime = 0;
            }else{
                $allowedTime = 120;
            }
            if (!$this->checkLastTime($smsLastTime, $currentTime, $allowedTime)) {
                return $this->createJsonResponse(array('error' => '请等待120秒再申请'));
            }

            $smsType = $request->request->get('sms_type');
            $this->checkSmsType($smsType, $currentUser);

            if (in_array($smsType, array('sms_bind','sms_registration'))) {
                $to = $request->request->get('to');
                $hasVerifiedMobile = (isset($currentUser['verifiedMobile'])&&(strlen($currentUser['verifiedMobile'])>0));
                if ($hasVerifiedMobile && ($to == $currentUser['verifiedMobile'])){
                    return $this->createJsonResponse(array('error' => '您已经绑定了这个手机'));
                }
            }

            if ($smsType == 'sms_forget_password') {
                $nickname = $request->request->get('nickname');
                if (strlen($nickname) == 0){
                    return $this->createJsonResponse(array('error' => '不存在用户昵称'));
                }
                $targetUser = $this->getUserService()->getUserByNickname($nickname);
                if (empty($targetUser)){
                    return $this->createJsonResponse(array('error' => '用户不存在'));    
                }
                if ((!isset($targetUser['verifiedMobile']) || (strlen($targetUser['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有被绑定的手机号'));
                }
                if ($targetUser['verifiedMobile'] != $request->request->get('to')) {
                    return $this->createJsonResponse(array('error' => '手机与用户名不匹配'));
                }
                $to = $targetUser['verifiedMobile'];
            }

            if (in_array($smsType, array('sms_user_pay', 'sms_forget_pay_password'))) {
                $user = $currentUser->toArray();
                if ((!isset($user['verifiedMobile']) || (strlen($user['verifiedMobile']) == 0))) {
                    return $this->createJsonResponse(array('error' => '用户没有被绑定的手机号'));
                }
                if ($user['verifiedMobile'] != $request->request->get('to')) {
                    return $this->createJsonResponse(array('error' => '您输入的手机号，不是已绑定的手机'));
                }
                $to = $user['verifiedMobile'];
            }

            if (!$this->checkPhoneNum($to)){
                return $this->createJsonResponse(array('error' => "手机号错误:{$to}"));
            }

            if ($this->debug) {
                $request->getSession()->set('to', '13758129341');
                $request->getSession()->set('sms_code', '357212');
                $request->getSession()->set('sms_last_time', time());
                $request->getSession()->set('sms_type', $smsType);

                return $this->createJsonResponse(array('ACK' => 'ok', 'debug' => 'true'));
            }

            $smsCode = $this->generateSmsCode();
            try {
                $result = $this->getEduCloudService()->sendSms($to, $smsCode, $smsType);
                if (isset($result['error'])) {
                    return $this->createJsonResponse(array('error' => "发送失败, {$result['error']}"));
                }
            } catch (\RuntimeException $e) {
                $message = $e->getMessage();
                return $this->createJsonResponse(array('error' => "发送失败, {$message}"));
            }

            $this->getLogService()->info('sms', $smsType, "对{$to}发送用于{$smsType}的验证短信{$smsCode}", $result);

            $request->getSession()->set('to', $to);
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

    private function checkLastTime($smsLastTime, $currentTime, $allowedTime = 120)
    {
        if (!((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime))) {
            return false;
        }
        return true;
    }

    private function checkSmsType($smsType, $user)
    {
        if (!in_array($smsType, array('sms_bind','sms_user_pay', 'sms_registration', 'sms_forget_password', 'sms_forget_pay_password'))) {
            throw new \RuntimeException('不存在的sms Type');
        }

        if ((!$user->isLogin()) && (in_array($smsType, array('sms_bind','sms_user_pay', 'sms_forget_pay_password')))) {
            throw new \RuntimeException('用户未登陆');
        }

        if ($this->getCloudSmsKey($smsType) != 'on') {
            throw new \RuntimeException('该使用场景未开启');
        }
    }

    private function getCloudSmsKey($key)
    {
        $setting = $this->getSettingService()->get('cloud_sms', array());
        if (isset($setting[$key])){
            return $setting[$key];
        }
        return null;
    }

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
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
