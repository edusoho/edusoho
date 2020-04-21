<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\ResourceSyncService;

class ResourceSyncServiceTest extends BaseTestCase
{
    public function testGetSync_whenCreated_thenGot()
    {
        $this->getResourceSyncService()->create();
        $this->getResourceSyncService()->getSync();
    }

    protected function mockResourceSyncFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'resourceType' =>
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