<?php

namespace Biz\OrderFacade;

use Codeages\Biz\Framework\Context\Biz;

class Currency
{
    public $isoCode = 'CNY';

    public $symbol = '￥';

    public $exchangeRate = 1;

    public function __construct(Biz $biz)
    {
        $coinSetting = $biz->service('System:SettingService')->get('coin', array());

        if (!empty($coinSetting['coin_enabled']) && $coinSetting['cash_model'] == 'currency') {
            $this->isoCode = $coinSetting['name'];
            $this->symbol = $coinSetting['name'];
            $this->exchangeRate = $coinSetting['cash_rate'];
        }
    }
}