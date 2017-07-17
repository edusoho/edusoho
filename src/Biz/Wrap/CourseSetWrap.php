<?php

namespace Biz\Wrap;

class CourseSetWrap extends BaseWrap
{
    public function price($courseSet)
    {
        $coinSetting = $this->getSettingService()->get('coin', array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
        ));

        if (0 == $courseSet['maxCoursePrice']) {
            $courseSet['priceText'] = $this->container->get('translator')->trans('course.block_grid.price_free');

            return $courseSet;
        }

        $unit = $this->container->get('translator')->trans('admin.account_center.RMB');
        $price = round($courseSet['minCoursePrice'], 2);
        $courseSet['priceCurrency'] = 'RMB';

        if ($coinSetting['coin_enabled'] && $coinSetting['cash_model'] == 'currency') {
            $courseSet['priceCurrency'] = 'coin';

            $unit = isset($coinSetting['coin_name']) ? $coinSetting['coin_name'] : $this->container->get('translator')->trans('finance.coin');
            $price = $courseSet['minCoursePriceShow'] = round($courseSet['minCoursePrice'] * $coinSetting['cash_rate'], 2);
            $courseSet['maxCoursePriceShow'] = round($courseSet['maxCoursePrice'] * $coinSetting['cash_rate'], 2);
        }

        if ($courseSet['minCoursePrice'] == $courseSet['maxCoursePrice']) {
            $courseSet['priceText'] = $price.$unit;
        } else {
            $courseSet['priceText'] = $this->container->get('translator')->trans('course.minimum_price.unit', array('%price%' => $price, '%unit%' => $unit));
        }

        return $courseSet;
    }

    protected function getSettingService()
    {
        return $this->container->get('biz')->service('System:SettingService');
    }

    protected function getWrapList()
    {
        $list = array('price');

        return $list;
    }
}
