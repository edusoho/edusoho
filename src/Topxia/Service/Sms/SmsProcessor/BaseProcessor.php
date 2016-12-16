<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Service\Common\ServiceKernel;

class BaseProcessor
{
    protected function getUsersMobile($userIds)
    {
        $mobiles = $this->getUserService()->findUnlockedUserMobilesByUserIds($userIds);
        $to      = implode(',', $mobiles);

        return $to;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
