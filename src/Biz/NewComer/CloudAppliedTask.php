<?php

namespace Biz\NewComer;

class CloudAppliedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $newcomerTask = $this->getSettingService()->get('newcomer_task', []);

        if (!empty($newcomerTask['cloud_applied_task']['status'])) {
            return true;
        }

        $storage = $this->getSettingService()->get('storage', []);

        if (!empty($storage['cloud_key_applied'])) {
            $this->doneTask('cloud_applied_task');

            return true;
        }

        return false;
    }
}
