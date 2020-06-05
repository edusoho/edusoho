<?php

namespace Tests\Unit\Goods\Dao;

use Biz\BaseTestCase;
use Tests\Unit\Base\BaseDaoTestCase;

class PurchaseVoucherDaoTest extends BaseDaoTestCase
{
    public function getDefaultMockFields()
    {
        return [
            'specsId' => 1,
            'goodsId' => 1,
            'orderId' => 1,
            'userId' => 1,
            'effectiveType' => 'date',
            'effectiveTime' => time(),
            'invalidTime' => time() + 86400,
        ];
    }

    public function test
}
