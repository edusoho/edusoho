<?php

namespace Biz\NewComer;

class CloudAppliedTask extends BaseNewcomer
{
    public function getStatus()
    {
        $storage = $this->getSettingService()->get('storage', array());

        if (!empty($storage['cloud_key_applied'])) {
            return true;
        }

        return false;
    }
}
