<?php
namespace Topxia\Service\Sms\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sms\SmsService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class SmsServiceImpl extends BaseService implements SmsService
{

    public function isOpen($smsType)
    {
        $cloudSmsSetting = $this->getSettingService()->get('cloud_sms');
        if ((isset($cloudSmsSetting['sms_enabled'])) && ($cloudSmsSetting['sms_enabled'] == '1') 
            && (isset($cloudSmsSetting[$smsType])) && ($cloudSmsSetting[$smsType] == '1')) {
            return true;
        }

        return false;
    }

    public function smsSend($smsType, $userIds, $parameters=array())
    {
        if ($this->isOpen($smsType)) {
            throw new Exception("云短信相关设置未开启!");
            
        }
        $smsCode = $this->generateSmsCode();
        $users = $this->getUserService()->findUsersByIds($userIds);
        $to = '';
        if (!empty($users)) {
            $verifiedMobiles = ArrayToolkit::column($users, 'verifiedMobile');
            $to = implode(',', $verifiedMobiles);
        }
        try {
                $api = CloudAPIFactory::create('leaf');
                $result = $api->post("/sms/{$api->getAccessKey()}/sendVerify", array('mobile' => $to, 'verify' => $smsCode, 'category' => $smsType, 'parameters' => $parameters));
            } catch (\RuntimeException $e) {
                throw new Exception("发送失败！", 1);
            }
        $result['to'] = $to;
        $result['smsCode'] = $smsCode;
        foreach ($users as $user) {
            $result['userId'] = $user['id'];
            $result['nickname'] = $user['nickname'];
            $this->getLogService()->info('sms', $smsType, "userId:{$user['id']},对{$to}发送用于{$smsType}的验证短信{$smsCode}", $result);     
        }
        return true;
    }
    

    private function generateSmsCode($length = 6)
    {
        $code = rand(0, 9);
        for ($i = 1; $i < $length; $i++) {
            $code = $code . rand(0, 9);
        }
        return $code;
    }

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

