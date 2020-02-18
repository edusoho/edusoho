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
        $this->getDestroyAccountRecordService()->deleteDestroyAccountRecord(1);
        $result = $this->getDestroyAccountRecordService()->getDestroyAccountRecord(1);
        $this->assertEquals($result, null);
    }

    public function testSearchDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();

        $result = $this->getDestroyAccountRecordService()->searchDestroyAccountRecords(array(), array(), 0, 1);

        $this->assertEquals(1, count($result));
    }

    public function testGetLastAuditDestroyAccountRecordByUserId()
    {
        $this->createDestroyAccountRecord();

        $result = $this->getDestroyAccountRecordService()->getLastAuditDestroyAccountRecordByUserId(1);
        $this->assertEquals(1, $result['userId']);
    }

    public function testCancelDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();
        $result = $this->getDestroyAccountRecordService()->cancelDestroyAccountRecord();

        $this->assertEquals('cancel', $result['status']);
    }

    public function testPassDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();
        $result = $this->getDestroyAccountRecordService()->passDestroyAccountRecord(1);

        $this->assertEquals('passed', $result);
    }

    public function testRejectDestroyAccountRecord()
    {
        $this->createDestroyAccountRecord();
        $result = $this->getDestroyAccountRecordService()->rejectDestroyAccountRecord(1, '不允许注销');

        $this->assertEquals('rejected', $result['status']);
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
