<?php

namespace Biz\OrderFacade;

use Codeages\Biz\Framework\Context\Biz;

class Currency
{
    public $isoCode = MajorCurrency::ISO_CODE;

    public $symbol = MajorCurrency::SYMBOL;

    public $prefix = MajorCurrency::PREFIX;

    public $suffix = MajorCurrency::SUFFIX;

    public $exchangeRate = MajorCurrency::EXCHANGE_RATE;

    /**
     * Number of digits after the decimal separator.
     *
     * @var int
     */
    public $precision = MajorCurrency::PRECISION;

    /**
     * Decimal part delimiter
     *
     * @var string
     */
    public $decimalDelimiter = MajorCurrency::DECIMAL_DELIMITER;

    /**
     * Thousand delimier
     *
     * @var string
     */
    public $thousandDelimiter = MajorCurrency::THOUSAND_DELIMITER;

    private $coinSetting = array();

    public function __construct(Biz $biz)
    {
        $coinSetting = $biz->service('System:SettingService')->get('coin', array());

        if ($coinSetting['coin_enabled'] && 'currency' == $coinSetting['cash_model']) {
            $this->isoCode = 'COIN';
            $this->symbol = $coinSetting['coin_name'];
            $this->prefix = '';
            $this->suffix = ' '.$coinSetting['coin_name'];
            $this->exchangeRate = $coinSetting['cash_rate'];
        }

        $this->coinSetting = $coinSetting;
    }

    public function formatParts($value)
    {
        $value = round($value * $this->exchangeRate, 2);

        $parts = array();

        if (0 > $value) {
            $parts['sign'] = '-';
        }

        if ($this->prefix) {
            $parts['prefix'] = $this->prefix;
        }

        $parts['integer'] = number_format(floor(abs($value)), 0, '', $this->thousandDelimiter);

        if (0 < $this->precision) {
            $parts['decimalDelimiter'] = $this->decimalDelimiter;
            $parts['decimal'] = str_pad(
                substr(
                    strval(abs(0 != $value ? $value : 1) * pow(10, $this->precision)),
                    -1 * $this->precision
                ),
                $this->precision,
                '0',
                STR_PAD_LEFT
            );
        }

        if ($this->suffix) {
            $parts['suffix'] = $this->suffix;
        }

        return $parts;
    }

    public function formatToCoinCurrency($value)
    {
        $value = round($value, 2);

        $parts = array();

        if (0 > $value) {
            $parts['sign'] = '-';
        }

        $parts['prefix'] = !empty($this->coinSetting['coin_name']) ? $this->coinSetting['coin_name'] : CoinCurrency::PREFIX;

        $parts['integer'] = number_format(floor(abs($value)), 0, '', CoinCurrency::THOUSAND_DELIMITER);

        if (0 < CoinCurrency::PRECISION) {
            $parts['decimalDelimiter'] = CoinCurrency::DECIMAL_DELIMITER;
            $parts['decimal'] = str_pad(
                substr(
                    strval(abs(0 != $value ? $value : 1) * pow(10, CoinCurrency::PRECISION)),
                    -1 * CoinCurrency::PRECISION
                ),
                CoinCurrency::PRECISION,
                '0',
                STR_PAD_LEFT
            );
        }

        $parts['suffix'] = !empty($this->coinSetting['coin_name']) ? $this->coinSetting['coin_name'] : CoinCurrency::SUFFIX;

        return $parts;
    }

    public function formatToMoneyCurrency($value)
    {
        $value = round($value, 2);

        $parts = array();

        if (0 > $value) {
            $parts['sign'] = '-';
        }

        if (MoneyCurrency::PREFIX) {
            $parts['prefix'] = MoneyCurrency::PREFIX;
        }

        $parts['integer'] = number_format(floor(abs($value)), 0, '', MoneyCurrency::THOUSAND_DELIMITER);

        if (0 < MoneyCurrency::PRECISION) {
            $parts['decimalDelimiter'] = MoneyCurrency::DECIMAL_DELIMITER;
            $parts['decimal'] = str_pad(
                substr(
                    strval(abs(0 != $value ? $value : 1) * pow(10, MoneyCurrency::PRECISION)),
                    -1 * MoneyCurrency::PRECISION
                ),
                MoneyCurrency::PRECISION,
                '0',
                STR_PAD_LEFT
            );
        }

        if (MoneyCurrency::SUFFIX) {
            $parts['suffix'] = MoneyCurrency::SUFFIX;
        }

        return $parts;
    }

    public function formatToMajorCurrency($value)
    {
        $value = round($value, 2);

        $parts = array();

        if (0 > $value) {
            $parts['sign'] = '-';
        }

        if (MajorCurrency::PREFIX) {
            $parts['prefix'] = MajorCurrency::PREFIX;
        }

        $parts['integer'] = number_format(floor(abs($value)), 0, '', MajorCurrency::THOUSAND_DELIMITER);

        if (0 < MajorCurrency::PRECISION) {
            $parts['decimalDelimiter'] = MajorCurrency::DECIMAL_DELIMITER;
            $parts['decimal'] = str_pad(
                substr(
                    strval(abs(0 != $value ? $value : 1) * pow(10, MajorCurrency::PRECISION)),
                    -1 * MajorCurrency::PRECISION
                ),
                MajorCurrency::PRECISION,
                '0',
                STR_PAD_LEFT
            );
        }

        if (MajorCurrency::SUFFIX) {
            $parts['suffix'] = MajorCurrency::SUFFIX;
        }

        return $parts;
    }

    public function convertToCoin($value)
    {
        if ($this->coinSetting['coin_enabled']) {
            return round(round($value, 2) * $this->coinSetting['cash_rate'], 2);
        }

        return $value;
    }

    public function convertToCNY($value)
    {
        if ($this->coinSetting['coin_enabled']) {
            return round($value / $this->coinSetting['cash_rate'], 2);
        }

        return $value;
    }
}
