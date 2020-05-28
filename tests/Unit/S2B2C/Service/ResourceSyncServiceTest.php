<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\ResourceSyncService;

class ResourceSyncServiceTest extends BaseTestCase
{
    public function testGetSync_whenCreated_thenGot()
    {
        $mockSync = $this->mockResourceSyncFields();
        $createdSync = $this->getResourceSyncService()->createSync($mockSync);
        $sync = $this->getResourceSyncService()->getSync($createdSync['id']);
        $this->assertEquals($createdSync, $sync);
    }

    public function testCreateSync_whenParamsCorrect_thenGot()
    {
        $mockSync = $this->mockResourceSyncFields();
        $createdSync = $this->getResourceSyncService()->createSync($mockSync);
        $this->assertEquals($mockSync['supplierId'], $createdSync['supplierId']);
    }

    public function testSearchSync_whenCreatedAny_thenSearch()
    {
        $mockSync0 = $this->mockResourceSyncFields();
        $mockSync1 = $this->mockResourceSyncFields(['localResourceId' => 2, 'remoteResourceId' => 200]);
        $mockSync2 = $this->mockResourceSyncFields(['supplierId' => 2, 'localResourceId' => 1, 'remoteResourceId' => 100]);
        $createdSync0 = $this->getResourceSyncService()->createSync($mockSync0);
        $createdSync1 = $this->getResourceSyncService()->createSync($mockSync1);
        $this->getResourceSyncService()->createSync($mockSync2);
        $results = $this->getResourceSyncService()->searchSyncs(['supplierId' => 1], ['localResourceId' => 'ASC'], 0, 10);
        $this->assertCount(2, $results);
        $this->assertEquals($createdSync0, $results[0]);
        $this->assertEquals($createdSync1, $results[1]);
    }

    public function testGetSyncBySupplierIdAndRemoteResourceIdAndResourceType_whenCreated_thenGot()
    {
        $created = $this->getResourceSyncService()->createSync($this->mockResourceSyncFields());
        $got = $this->getResourceSyncService()->getSyncBySupplierIdAndRemoteResourceIdAndResourceType($created['supplierId'], $created['remoteResourceId'], $created['resourceType']);
        $this->assertEquals($created, $got);
    }

    public function testFindSyncBySupplierIdAndRemoteResourceIdsAndResourceType_whenCreatedThree_thenFindOne()
    {
        $mockSync0 = $this->mockResourceSyncFields();
        $mockSync1 = $this->mockResourceSyncFields(['localResourceId' => 2, 'remoteResourceId' => 300]);
        $mockSync2 = $this->mockResourceSyncFields(['supplierId' => 2, 'localResourceId' => 1, 'remoteResourceId' => 100]);
        $createdSync0 = $this->getResourceSyncService()->createSync($mockSync0);
        $this->getResourceSyncService()->createSync($mockSync1);
        $this->getResourceSyncService()->createSync($mockSync2);
        $results = $this->getResourceSyncService()->findSyncBySupplierIdAndRemoteResourceIdsAndResourceType(1, [100, 200], 'task');
        $this->assertCount(1, $results);
        $this->assertEquals($createdSync0, reset($results));
    }

    protected function mockResourceSyncFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'resourceType' => 'task',
            'localResourceId' => 1,
            'remoteResourceId' => 100,
            'syncTime' => time(),
        ], $customFields);
    }

    /**
     * @return ResourceSyncService
     */
    protected function getResourceSyncService()
    {
        return $this->createService('S2B2C:ResourceSyncService');
    }
}
