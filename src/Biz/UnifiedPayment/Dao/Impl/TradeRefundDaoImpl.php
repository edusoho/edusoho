<?php

namespace Biz\UnifiedPayment\Dao\Impl;

use Biz\UnifiedPayment\Dao\TradeRefundDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TradeRefundDaoImpl extends GeneralDaoImpl implements TradeRefundDao
{
    protected $table = 'unified_payment_trade_refund';

    public function findByTradeSn($sn)
    {
        return $this->findByFields([
            'tradeSn' => $sn,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'refundResult' => 'json',
            ],
            'conditions' => [
            ],
        ];
    }
}
