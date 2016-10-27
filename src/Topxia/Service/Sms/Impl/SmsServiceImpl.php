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

        $needVerified = false;
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds, $needVerified);
        $to      = implode(',', $mobiles);

        try {
            $api    = CloudAPIFactory::create('leaf');
            $result = $api->post("/sms/send", array('mobile' => $to, 'category' => $smsType, 'description' => $description, 'parameters' => $parameters));
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($this->getKernel()->trans('发送失败！'));
        }

        $message = $this->getKernel()->trans('对');
        $message .= $to;
        $message .= $this->getKernel()->trans('发送用于%Type%的通知短信',array('%Type%'=>$smsType));
        $this->getLogService()->info('sms', $smsType, $message, $mobiles);

        return true;
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
