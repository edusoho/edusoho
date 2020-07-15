<?php

namespace AppBundle\Component\Wrapper;

use Topxia\Service\Common\ServiceKernel;

class ItemBankExerciseWrapper extends Wrapper
{
    public function price($exercise)
    {
        $coinSetting = $this->getSettingService()->get('coin', array(
            'coin_enabled' => 0,
            'cash_model' => 'none',
            'cash_rate' => 1,
        ));

        $priceWrapper = array(
            'priceText' => $exercise['price'],
            'currencyType' => 'RMB',
            'currency' => $this->container->get('translator')->trans('admin.account_center.RMB'),
        );

        if (0 == $exercise['price']) {
            $priceWrapper['priceText'] = $this->container->get('translator')->trans('course.block_grid.price_free');

            $exercise['priceWrapper'] = $priceWrapper;

            return $exercise;
        }

        $price = round($exercise['price'], 2);

        if ($coinSetting['coin_enabled'] && 'currency' == $coinSetting['cash_model']) {
            $priceWrapper['currencyType'] = 'coin';
            $priceWrapper['currency'] = $coinSetting['coin_name'] ?: $this->container->get('translator')->trans('finance.coin');
            $price = round($exercise['price'] * $coinSetting['cash_rate'], 2);
        }

        $priceWrapper['priceText'] = $price.$priceWrapper['currency'];

        $exercise['priceWrapper'] = $priceWrapper;

        return $exercise;
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
