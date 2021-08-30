<?php

namespace Biz\System\SettingModule;

use Codeages\Biz\Framework\Context\Biz;

class SettingMaintainer
{
    /**
     * @return CourseSetting
     */
    public static function courseSetting(Biz $biz)
    {
        return new CourseSetting($biz);
    }

    /**
     * @return ClassroomSetting
     */
    public static function classroomSetting(Biz $biz)
    {
        return new ClassroomSetting($biz);
    }
}
