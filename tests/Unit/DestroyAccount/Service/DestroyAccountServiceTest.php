<?php

namespace Tests\Unit\DestroyAccount\Service;

use Biz\BaseTestCase;
use Biz\DestroyAccount\Service\DestroyAccountRecordService;

class DestroyAccountServiceTest extends BaseTestCase
{
    public function testGetDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();
        $result = $this->getDestroyAccountRecordService()->getDestroyAccountRecord(1);

        $this->assertEquals(1, $result['userId']);
        $this->assertEquals(1, $result['id']);
    }

    public function testUpdateDestroyAccountRecord()
    {
        $origin = $this->createDestroyAccountRecord();

        $result = $this->getDestroyAccountRecordService()->updateDestroyAccountRecord($origin['id'], array('status' => 'passed'));

        $this->assertEquals('passed', $result['status']);
    }

    public function testCreateDestroyAccountRecord()
    {
        $origin = $this->createDestroyAccountRecord();
        $this->assertEquals($origin['status'], 'audit');
    }

    public function testDeleteDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();
        $this->getDestroyAccountRecordService()->deleteDiscoveryColumn(1);
        $result = $this->getDestroyAccountRecordService()->getDiscoveryColumn(1);
        $this->assertEquals($result, null);
    }

    public function testSearchDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();

        $result = $this->getDestroyAccountRecordService()->searchDestroyAccountRecords(array(), array(), 0, 1);

        $this->assertEquals(1, count($result));
    }

    public function testGetLastDestroyAccountRecordByUserId()
    {
        $this->createDestroyAccountRecord();
        sleep(1);
        $fields = array(
            'userId' => 2,
            'nickname' => 'test',
            'reason' => '释放手机号',
            'status' => 'audit',
        );
        $this->getDestroyAccountRecordService()->createDestroyAccountRecord($fields);

        $result = $this->getDestroyAccountRecordService()->getLastDestroyAccountRecordByUserId(1);
        $this->assertEquals(2, $result['userId']);
    }

    public function testCountDestroyAccountRecords()
    {
        $this->createDestroyAccountRecord();

        $result = $this->getDestroyAccountRecordService()->countDestroyAccountRecords(array());
        $this->assertEquals(1, $result);
    }

    private function createDestroyAccountRecord()
    {
        $fields = array(
            'userId' => 1,
            'nickname' => 'test',
            'reason' => '释放手机号',
            'status' => 'audit',
        );

        return $this->getDestroyAccountRecordService()->createDestroyAccountRecord($fields);
    }

    /**
     * @return DestroyAccountRecordService
     */
    protected function getDestroyAccountRecordService()
    {
        return $this->createService('DestroyAccount:DestroyAccountRecordService');
    }
}
