<?php
namespace Topxia\Service\Sms\SmsProcessor;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\NumberToolkit;
use Exception;

class BaseProcessor {

	protected function unsetUsersByMobile($userIds)
    {
        if (!empty($userIds)) {
            $users = $this->getUserService()->findUsersByIds($userIds);
            $to = '';
            foreach ($users as $key => $value ) {
                if (strlen($value['verifiedMobile']) == 0) {
                    unset($users[$key]);
                }
            }
            return $users;
        }
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