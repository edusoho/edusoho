<?php

namespace Biz\NewComer;

class AuthSettingTask extends BaseNewcomer
{
    public function getStatus()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', array());
        if (!empty($newcomerTask['auth_setting_task']['status'])) {
            return true;
        }

        return false;
    }
}
