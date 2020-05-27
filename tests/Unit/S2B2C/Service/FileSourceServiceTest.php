<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\FileSourceService;

class FileSourceServiceTest extends BaseTestCase
{
    public function testGetFullFileInfo_withS2B2CIds()
    {
        $this->mockBiz('S2B2C:ProductService', [
            ['functionName' => 'getByTypeAndLocalResourceId', 'returnValue' => ['remoteProductId' => 1]],
        ]);

        $file = ['s2b2cGlobalId' => 1, 's2b2cHashId' => 2, 'targetId' => 1];

        $result = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $expect = ['s2b2cGlobalId' => 1, 's2b2cHashId' => 2, 'hashId' => 2, 'globalId' => 1, 'targetId' => 1, 'sourceTargetId' => 1];
        $this->assertEquals($expect, $result);

        $file = ['globalId' => 3, 'hashId' => 4, 'targetId' => 1];

        $result = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $expect = ['globalId' => 3, 'hashId' => 4, 'targetId' => 1, 'sourceTargetId' => 1];

        $this->assertEquals($expect, $result);
    }

    public function testGetFullFileInfo_thenReturnEmptySourceTargetId()
    {
        $this->mockBiz('S2B2C:ProductService', [
            ['functionName' => 'getByTypeAndLocalResourceId', 'returnValue' => []],
        ]);

        $file = ['globalId' => 1, 'hashId' => 2, 'targetId' => 1];

        $result = $this->getS2B2CFileSourceService()->getFullFileInfo($file);
        $expect = ['globalId' => 1, 'hashId' => 2, 'targetId' => 1, 'sourceTargetId' => 0];
        $this->assertEquals($expect, $result);
    }

    /**
     * @return FileSourceService
     */
    protected function getS2B2CFileSourceService()
    {
        return $this->createService('S2B2C:FileSourceService');
    }
}
