<?php

namespace Tests\Unit\Coupon\Dao;

use AppBundle\Common\ArrayToolkit;
use Tests\Unit\Base\BaseDaoTestCase;

class CouponDaoTest extends BaseDaoTestCase
{
    public function testFindByIds()
    {
        $expected = $this->_createDatas();
        $ids = ArrayToolkit::column($expected, 'id');
        $results = $this->getDao()->findByIds($ids);
        $this->assertEquals(count($ids), count($results));
    }

    public function testGetByCode()
    {
        $this->_createDatas();
        $result = $this->getDao()->getByCode('x22232423');
        $this->assertNotEmpty($result);
        $this->assertEquals('x22232423', $result['code']);
    }

    public function testFindByBatchId()
    {
        $this->mockDataObject(array('batchId' => 2));
        $results = $this->getDao()->findByBatchId(2, 0, 100);
        $this->assertEquals(1, count($results));
    }

    public function getDefaultMockFields()
    {
        return array(
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'using',
            'rate' => 10,
            'deadline' => time(),
        );
    }

    public function testDeleteByBatch()
    {
        $this->mockDataObject(array('batchId' => 2));
        $results = $this->getDao()->findByBatchId(2, 0, 100);
        $this->assertEquals(1, count($results));
        $this->getDao()->deleteByBatch(2);

        $results = $this->getDao()->findByBatchId(2, 0, 100);
        $this->assertEquals(0, count($results));
    }

    public function testSearch()
    {
        $this->mockDataObject(array('batchId' => 2, 'orderTime' => time()));

        $results = $this->getDao()->search(
            array(
                'code' => 'x2223242',
                'batchIdNotEqual' => 0,
                'startDateTime' => date('Y-m-d', time() - 1000),
                'endDateTime' => date('Y-m-d', time() + 1000),
                'useStartDateTime' => date('Y-m-d', time() - 1000),
                'useEndDateTime' => date('Y-m-d', time() + 1000),
            ),
            array('createdTime' => 'DESC'),
            0,
            100
        );

        $this->assertEquals(1, count($results));
    }

    private function _createDatas()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('code' => 'x22232423', 'batchId' => 1));
        $expected[] = $this->mockDataObject(array('code' => 'x22232424', 'batchId' => 1));
        $expected[] = $this->mockDataObject(array('code' => 'x22232425', 'batchId' => 2));
        $expected[] = $this->mockDataObject(array('code' => 'x22232426', 'batchId' => 2));

        return $expected;
    }
}
