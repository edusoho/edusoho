<?php

namespace Tests\Unit\File;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\BaseTestCase;
use Mockery;

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
