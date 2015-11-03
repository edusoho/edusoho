<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class BaseProcessor {

	protected function getUsersMobile($userIds)
    {
        $to = '';
        if (!empty($userIds)) {
            $users = $this->getUserService()->findUsersByIds($userIds);
            $to = '';
            foreach ($users as $key => $value ) {
                if (strlen($value['verifiedMobile']) == 0 || $value['locked']) {
                    unset($users[$key]);
                }
            }
            if ($users) {
                $verifiedMobiles = ArrayToolkit::column($users, 'verifiedMobile');
                $to = implode(',', $verifiedMobiles);
            }
        }
        return $to;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

}