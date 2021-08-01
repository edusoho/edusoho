<?php

namespace Biz\System\SettingModule;

class CourseSetting extends AbstractSetting
{
    public function getBaseCourseSetting()
    {
        SettingMaintainer::courseSetting($this->biz)->getBaseCourseSetting();
    }
}
