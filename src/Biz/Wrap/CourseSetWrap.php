<?php

namespace Biz\Wrap;

class CourseSetWrap extends BaseWrap
{
    public function handle($courseSet, $function)
    {
        $isCallAble = is_callable(array($this, $function));
        if ($isCallAble) {
            $courseSet = call_user_func(array($this, $function), $courseSet);
        }
        return $courseSet;
    }

    public function price($courseSet)
    {
        $coinSetting = $this->getSettingService()->get('coin',array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
        ));

        if (0 == $courseSet['maxCoursePrice']) {
            $courseSet['showPrice'] = $this->container->get('translator')->trans('course.block_grid.price_free');
            return $courseSet;
        }

        $unit = $this->container->get('translator')->trans('admin.account_center.RMB');
        $price = round($courseSet['minCoursePrice'], 2);
        if ($coinSetting['coin_enabled'] && $coinSetting['cash_model'] == 'currency') {
            $unit = isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : $this->container->get('translator')->trans('finance.coin');
        }

        if ($courseSet['minCoursePrice']== $courseSet['maxCoursePrice']) {
            $courseSet['showPrice'] = $price.$unit;
        } else {
            $courseSet['showPrice'] =  $this->container->get('translator')->trans('course.minimum_price.unit', array('%price%'=>$price, '%unit%'=> $unit));
        }

        return $courseSet;
    }

    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }
}
