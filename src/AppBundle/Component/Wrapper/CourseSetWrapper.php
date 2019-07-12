<?php

namespace AppBundle\Component\Wrapper;

use Topxia\Service\Common\ServiceKernel;

class CourseSetWrapper extends Wrapper
{
    public function price($courseSet)
    {
        $coinSetting = $this->getSettingService()->get('coin', array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
        ));

        $priceWrapper = array(
            'priceText' => $courseSet['maxCoursePrice'],
            'currencyType' => 'RMB',
            'currency' => $this->container->get('translator')->trans('admin.account_center.RMB'),
        );

        if (0 == $courseSet['maxCoursePrice']) {
            $priceWrapper['priceText'] = $this->container->get('translator')->trans('course.block_grid.price_free');

            $courseSet['priceWrapper'] = $priceWrapper;

            return $courseSet;
        }

        $price = round($courseSet['minCoursePrice'], 2);

        if ($coinSetting['coin_enabled'] && 'currency' == $coinSetting['cash_model']) {
            $priceWrapper['currencyType'] = 'coin';
            $priceWrapper['currency'] = $coinSetting['coin_name'] ?: $this->container->get('translator')->trans('finance.coin');
            $price = round($courseSet['minCoursePrice'] * $coinSetting['cash_rate'], 2);
        }

        if ($courseSet['minCoursePrice'] == $courseSet['maxCoursePrice']) {
            $priceWrapper['priceText'] = $price.$priceWrapper['currency'];
        } else {
            $priceWrapper['priceText'] = $this->container->get('translator')->trans('course.minimum_price.unit', array('%price%' => $price, '%unit%' => $priceWrapper['currency']));
        }

        $courseSet['priceWrapper'] = $priceWrapper;

        return $courseSet;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }

    protected function getWrapList()
    {
        return array('price');
    }
}
