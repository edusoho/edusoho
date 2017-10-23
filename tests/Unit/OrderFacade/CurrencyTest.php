<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\Currency;
use Biz\System\Service\SettingService;

class CurrencyTest extends BaseTestCase
{
    public function testFormatParts()
    {
        $coinSetting = array(
            'coin_enabled' => 0,
            'cash_model' => 'currency',
            'coin_name' => '',
            'cash_rate' => 1,
        );
        $this->getSettingService()->set('coin', $coinSetting);

        $currency = new Currency($this->getBiz());

        $this->assertEquals('￥1024.11', implode('', $currency->formatParts('1024.11')));

        $currency->thousandDelimiter = ',';

        $this->assertEquals('￥1,024.11', implode('', $currency->formatParts('1024.11')));

        $currency->precision = 1;

        $this->assertEquals('￥1,024.1', implode('', $currency->formatParts('1024.11')));

        $currency->precision = 2;

        $this->assertEquals('￥1,024.00', implode('', $currency->formatParts('1024')));

        $currency->thousandDelimiter = '';
        $currency->prefix = '';
        $currency->suffix = '$';

        $this->assertEquals('1024.20$', implode('', $currency->formatParts('1024.2')));
    }

    public function testFormatPartsWithCoin()
    {
        $coin = array(
            'coin_enabled' => 1,
            'cash_model' => 'currency',
            'coin_name' => '虚币',
            'cash_rate' => 2,
        );

        $this->getSettingService()->set('coin', $coin);

        $currency = new Currency($this->getBiz());

        $this->assertEquals('2048.00 虚币', implode('', $currency->formatParts('1024')));
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
