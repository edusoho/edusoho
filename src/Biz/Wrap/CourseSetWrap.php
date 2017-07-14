<?php

namespace Biz\Wrap;

class CourseSetWrap extends BaseWrap
{
    public function handle($courseSet, $function)
    {

        $isCallAble = is_callable(array($this, $function));
        if ($isCallAble) {
            $courseSet = call_user_func(array($this, $function), array($courseSet));
        }
        return $courseSet;
    }

    public function price($courseSet)
    {
        $settingCoin = $this->getSettingService()->get('coin');
        var_dump($settingCoin);
        exit;
    }

    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }
}
