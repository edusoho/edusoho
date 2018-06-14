<?php

namespace Tests\Unit\CloudData\Service;

use Biz\BaseTestCase;

class CloudDataServiceTest extends BaseTestCase
{
    public function testPush()
    {
    }

    protected function getCloudDataService()
    {
        return $this->biz->service('CloudData:CloudDataService');
    }

    protected function getCloudDataDao()
    {
        return $this->biz->dao('CloudData:CloudDataDao');
    }
}
