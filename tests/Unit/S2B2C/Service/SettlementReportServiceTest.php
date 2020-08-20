<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Dao\ProductReportDao;
use Biz\S2B2C\Service\ProductReportService;

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
        $record = $this->getProductReportService()->create($initData);

        $this->assertEquals(ProductReportService::STATUS_CREATED, $record['status']);
    }

    public function testGetById()
    {
        $custom = ['nickname' => 'codeages'];
        $record = $this->simpleCreate($custom);
        $get = $this->getProductReportService()->getById($record['id']);

        $this->assertEquals($custom['nickname'], $get['nickname']);
    }

    public function testUpdateFailedReason()
    {
        $reason = '???';
        $record = $this->simpleCreate();
        $this->getProductReportService()->updateFailedReason($record['id'], $reason);
        $get = $this->getProductReportService()->getById($record['id']);

        $this->assertEquals(ProductReportService::STATUS_FAILED, $get['status']);
        $this->assertEquals($reason, $get['reason']);
    }

    public function testUpdateStatusToSent()
    {
        $record = $this->simpleCreate();
        $this->getProductReportService()->updateStatusToSent($record['id']);
        $get = $this->getProductReportService()->getById($record['id']);

        $this->assertEquals(ProductReportService::STATUS_SENT, $get['status']);
    }

    public function testUpdateStatusToSucceed()
    {
        $record = $this->simpleCreate();
        $this->getProductReportService()->updateStatusToSucceed($record['id']);
        $get = $this->getProductReportService()->getById($record['id']);

        $this->assertEquals(ProductReportService::STATUS_SUCCEED, $get['status']);
    }

    protected function getInitSettlementReportData($custom = [])
    {
        return array_merge([
            's2b2cProductId' => 1,
            'userId' => 1,
            'type' => ProductReportService::TYPE_JOIN_COURSE,
            'orderId' => 1,
        ], $custom);
    }

    protected function simpleCreate($custom = [])
    {
        return $this->getProductReportDao()->create(array_merge([
            'supplierId' => 1,
            'productId' => 1,
            'type' => ProductReportService::TYPE_JOIN_COURSE,
            'userId' => 1,
            'nickname' => 'test',
            'orderId' => 1,
            'status' => ProductReportService::STATUS_CREATED,
            'reason' => '',
        ], $custom));
    }

    /**
     * @return ProductReportService
     */
    protected function getProductReportService()
    {
        return $this->biz->service('S2B2C:ProductReportService');
    }

    /**
     * @return ProductReportDao
     */
    protected function getProductReportDao()
    {
        return $this->biz->dao('S2B2C:ProductReportDao');
    }
}