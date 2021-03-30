<?php

namespace Biz\NewComer;

class ManagementQualificationsTask extends BaseNewcomer
{
    public function getStatus()
    {
        $qualificationsSetting = $this->getSettingService()->get('qualifications', []);
        if (!empty($qualificationsSetting['icp'])) {
            return true;
        }
    }
}
