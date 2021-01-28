<?php

namespace Tests\Unit\Goods\Dao;

use AppBundle\Common\ArrayToolkit;
use Biz\Goods\Dao\PurchaseVoucherDao;
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

    public function testGetBySpecId()
    {
        $created = $this->mockDataObject();
        $result = $this->getDao()->getBySpecsId($created['id']);
        $this->assertEquals($created, $result);
    }

    public function testFindByIds()
    {
        $results = $this->getDao()->findByIds([]);
        $this->assertEquals([], $results);
        $created1 = $this->mockDataObject();
        $created2 = $this->mockDataObject(['goodsId' => 2]);

        $results = ArrayToolkit::index($this->getDao()->findByIds([$created1['id'], $created2['id']]), 'id');
        $this->assertCount(2, $results);
        $this->assertEquals(ArrayToolkit::column($results, 'id'), [$created1['id'], $created2['id']]);
        $this->assertEquals($created2, $results[$created2['id']]);
    }

    /**
     * @return PurchaseVoucherDao
     *
     * @throws \Exception
     */
    protected function getDao()
    {
        return parent::getDao();
    }
}
