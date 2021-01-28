<?php

namespace AppBundle\Component\Wrapper;

use Topxia\Service\Common\ServiceKernel;

class ClassroomWrapper extends Wrapper
{
    public function price($classroom)
    {
        $coinSetting = $this->getSettingService()->get('coin', array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
        ));

        $priceWrapper = array(
            'priceText' => $classroom['price'],
            'currencyType' => 'RMB',
            'currency' => $this->container->get('translator')->trans('admin.account_center.RMB'),
        );

        if (0 == $classroom['price']) {
            $priceWrapper['priceText'] = $this->container->get('translator')->trans('course.block_grid.price_free');

            $classroom['priceWrapper'] = $priceWrapper;

            return $classroom;
        }

        $price = round($classroom['price'], 2);

        if ($coinSetting['coin_enabled'] && 'currency' == $coinSetting['cash_model']) {
            $priceWrapper['currencyType'] = 'coin';
            $priceWrapper['currency'] = $coinSetting['coin_name'] ?: $this->container->get('translator')->trans('finance.coin');
            $price = round($classroom['price'] * $coinSetting['cash_rate'], 2);
        }

        $priceWrapper['priceText'] = $price.$priceWrapper['currency'];
        $classroom['priceWrapper'] = $priceWrapper;

        return $classroom;
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
