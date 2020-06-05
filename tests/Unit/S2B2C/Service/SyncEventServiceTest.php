<?php

namespace Tests\Unit\S2B2C\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\S2B2C\Dao\SyncEventDao;
use Biz\S2B2C\Service\SyncEventService;

class SyncEventServiceTest extends BaseTestCase
{
    public function testSearchSyncEvent()
    {
        $result = $this->createSyncEvent();
        $results = $this->getSyncEventService()->searchSyncEvent(['id' => $result['id']], [], 0, 10);
        $this->assertEquals($result, reset($results));
    }

    public function testConfirmByEvents()
    {
        $result = $this->createSyncEvent();
        $this->assertEquals(0, $result['isConfirm']);
        $this->getSyncEventService()->confirmByEvents(1, 'modifyPrice');
        $update = $this->getSyncEventDao()->get($result['id']);
        $this->assertEquals(1, $update['isConfirm']);
    }

    public function testFindNotifyByCourseSetIds()
    {
        $this->mockBiz('S2B2C:S2B2CFacadeService', [
            [
                'functionName' => 'getS2B2CConfig',
                'returnValue' => [
                    'supplierId' => 1,
                ],
            ],
        ]);
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'findCoursesByCourseSetIds',
                'returnValue' => [
                    [
                        'id' => 1,
                        'courseSetId' => 1,
                    ],
                    [
                        'id' => 2,
                        'courseSetId' => 2,
                    ],
                ],
            ],
        ]);

        $this->mockBiz('S2B2C:ProductService', [
            [
                'functionName' => 'findProductsBySupplierIdAndProductTypeAndLocalResourceIds',
                'returnValue' => [
                    [
                        'id' => 1,
                        'remoteResourceId' => 1,
                        'localResourceId' => 1,
                    ],
                    [
                        'id' => 2,
                        'remoteResourceId' => 2,
                        'localResourceId' => 2,
                    ],
                ],
            ],
        ]);
        $this->createSyncEvent(['productId' => 1]);
        $this->createSyncEvent(['productId' => 2]);
        $results = $this->getSyncEventService()->findNotifyByCourseSetIds([1, 2]);
        $this->assertCount(2, $results);
        $this->assertEquals([1, 2], ArrayToolkit::column($results, 'productId'));
    }

    protected function createSyncEvent($custom = [])
    {
        return $this->getSyncEventDao()->create(array_merge([
            'event' => 'modifyPrice',
            'data' => ['new' => [
                'suggestionPrice' => 10,
                'cooperationPrice' => 19,
            ]],
            'isConfirm' => 0,
            'productId' => 1,
        ], $custom));
    }

    /**
     * @return SyncEventDao
     */
    protected function getSyncEventDao()
    {
        return $this->createDao('S2B2C:SyncEventDao');
    }

    /**
     * @return SyncEventService
     */
    protected function getSyncEventService()
    {
        return $this->createService('S2B2C:SyncEventService');
    }
}
