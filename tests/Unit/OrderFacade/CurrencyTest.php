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

    public function testFormatToCoinCurrency()
    {
        $coinSetting = array(
            'coin_enabled' => 0,
            'cash_model' => 'currency',
            'coin_name' => '测试币',
            'cash_rate' => 1,
        );
        $this->getSettingService()->set('coin', $coinSetting);
        $currency = new Currency($this->getBiz());
        $result = $currency->formatToCoinCurrency(-1111);
        
        $this->assertEquals('-', $result['sign']);
        $this->assertEquals('测试币', $result['prefix']);
        $this->assertEquals('1111', $result['integer']);
        $this->assertEquals('00', $result['decimal']);
    }

    public function testFormatToMoneyCurrency()
    {
        $coinSetting = array(
            'coin_enabled' => 0,
            'cash_model' => 'currency',
            'coin_name' => '测试币',
            'cash_rate' => 1,
        );
        $this->getSettingService()->set('coin', $coinSetting);
        $currency = new Currency($this->getBiz());
        $result = $currency->formatToMoneyCurrency(-1111);

        $this->assertEquals('-', $result['sign']);
        $this->assertEquals('￥', $result['prefix']);
        $this->assertEquals('1111', $result['integer']);
        $this->assertEquals('00', $result['decimal']);
    }

    public function testFormatToMajorCurrency()
    {
        $coinSetting = array(
            'coin_enabled' => 0,
            'cash_model' => 'currency',
            'coin_name' => '测试币',
            'cash_rate' => 1,
        );
        $this->getSettingService()->set('coin', $coinSetting);
        $currency = new Currency($this->getBiz());
        $result = $currency->formatToMajorCurrency(-1111);

        $this->assertEquals('-', $result['sign']);
        $this->assertEquals('￥', $result['prefix']);
        $this->assertEquals('1111', $result['integer']);
        $this->assertEquals('00', $result['decimal']);
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
