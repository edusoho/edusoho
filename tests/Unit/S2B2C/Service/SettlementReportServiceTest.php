<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Dao\SettlementReportDao;
use Biz\S2B2C\Service\SettlementReportService;

class SettlementReportServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $this->mockBiz('S2B2C:S2B2CFacadeService', [
            [
                'functionName' => 'getSupplier',
                'returnValue' => [
                    'id' => 1,
                ],
                'runTimes' => 1,
            ],
        ]);

        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUser',
                'returnValue' => [
                    'id' => 1,
                    'nickname' => 'test'
                ],
                'runTimes' => 1,
            ],
        ]);

        $initData = $this->getInitSettlementReportData();
        $record = $this->getSettlementReportService()->create($initData);

        $this->assertEquals(SettlementReportService::STATUS_CREATED, $record['status']);
    }

    public function testGetById()
    {
        $custom = ['nickname' => 'codeages'];
        $record = $this->simpleCreate($custom);
        $get = $this->getSettlementReportService()->getById($record['id']);

        $this->assertEquals($custom['nickname'], $get['nickname']);
    }

    public function testUpdateFailedReason()
    {
        $reason = '???';
        $record = $this->simpleCreate();
        $this->getSettlementReportService()->updateFailedReason($record['id'], $reason);
        $get = $this->getSettlementReportService()->getById($record['id']);

        $this->assertEquals(SettlementReportService::STATUS_FAILED, $get['status']);
        $this->assertEquals($reason, $get['reason']);
    }

    public function testUpdateStatusToSent()
    {
        $record = $this->simpleCreate();
        $this->getSettlementReportService()->updateStatusToSent($record['id']);
        $get = $this->getSettlementReportService()->getById($record['id']);

        $this->assertEquals(SettlementReportService::STATUS_SENT, $get['status']);
    }

    public function testUpdateStatusToSucceed()
    {
        $record = $this->simpleCreate();
        $this->getSettlementReportService()->updateStatusToSucceed($record['id']);
        $get = $this->getSettlementReportService()->getById($record['id']);

        $this->assertEquals(SettlementReportService::STATUS_SUCCEED, $get['status']);
    }

    protected function getInitSettlementReportData($custom = [])
    {
        return array_merge([
            's2b2cProductId' => 1,
            'userId' => 1,
            'type' => SettlementReportService::TYPE_JOIN_COURSE,
            'orderId' => 1,
        ], $custom);
    }

    protected function simpleCreate($custom = [])
    {
        return $this->getSettlementReportDao()->create(array_merge([
            'supplierId' => 1,
            'productId' => 1,
            'type' => SettlementReportService::TYPE_JOIN_COURSE,
            'userId' => 1,
            'nickname' => 'test',
            'orderId' => 1,
            'status' => SettlementReportService::STATUS_CREATED,
            'reason' => '',
        ], $custom));
    }

    /**
     * @return SettlementReportService
     */
    protected function getSettlementReportService()
    {
        return $this->biz->service('S2B2C:SettlementReportService');
    }

    /**
     * @return SettlementReportDao
     */
    protected function getSettlementReportDao()
    {
        return $this->biz->dao('S2B2C:SettlementReportDao');
    }
}