<?php

namespace Biz\OrderFacade;

use Codeages\Biz\Framework\Context\Biz;

class Currency
{
    public $isoCode = 'CNY';

    public $symbol = '￥';

    public $prefix = '￥';

    public $suffix = '';

    public $exchangeRate = 1;

    /**
     * Number of digits after the decimal separator.
     *
     * @var int
     */
    public $precision = 2;

    /**
     * Decimal part delimiter
     *
     * @var string
     */
    public $decimalDelimiter = '.';

    /**
     * Thousand delimier
     *
     * @var string
     */
    public $thousandDelimiter = '';

    public function __construct(Biz $biz)
    {
        $coinSetting = $biz->service('System:SettingService')->get('coin', array());

        if (!empty($coinSetting['coin_enabled']) && $coinSetting['cash_model'] == 'currency') {
            $this->isoCode = 'COIN';
            $this->symbol = $coinSetting['coin_name'];
            $this->prefix = '';
            $this->suffix = $coinSetting['coin_name'];
            $this->exchangeRate = $coinSetting['cash_rate'];
        }
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
                    strval(abs($value != 0 ? $value : 1) * pow(10, $this->precision)),
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
}
