<?php

namespace Tests\Unit\Face\Service;

use Biz\BaseTestCase;
use Biz\Face\Service\FaceService;

class FaceServiceTest extends BaseTestCase
{
    public function testGetAiFaceSdk()
    {
        $result = $this->getFaceService()->getAiFaceSdk();
        $this->assertEmpty($result);
    }

    public function testCreateFaceLog()
    {
        $result = $this->getFaceService()->createFaceLog(array('userId' => 1, 'status' => 'test', 'sessionId' => 9));
        $this->assertEquals(1, $result['userId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateFaceLogWithErrorParam()
    {
        $this->getFaceService()->createFaceLog(array());
    }

    public function testCountFaceLog()
    {
        $count = $this->getFaceService()->countFaceLog(array('userId' => 1));
        $this->assertEquals(0, $count);
    }

    /**
     * @return FaceService
     */
    protected function getFaceService()
    {
        return $this->createService('Face:FaceService');
    }
}
