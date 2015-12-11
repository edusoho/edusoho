<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class SensitiveWordServiceTest extends BaseTestCase
{
    public function testAddQuestionMarkerResult()
    {
        $result = array(
            'markerId'         => 1,
            'questionMarkerId' => 2,
            'userId'           => 1,
            'status'           => 'none'

        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);
        $this->assertNotNull($savedResult);
        $this->assertEquals($result['markerId'], $savedResult['markerId']);
        $this->assertEquals($result['questionMarkerId'], $savedResult['questionMarkerId']);
        $this->assertEquals($result['userId'], $savedResult['userId']);
        $this->assertEquals($result['status'], $savedResult['status']);
    }

    public function testUpdateQuestionMarkerResult()
    {
        $result = array(
            'markerId'         => 3,
            'questionMarkerId' => 4,
            'userId'           => 6,
            'status'           => 'noAnswer'

        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);

        $result = array(
            'markerId'         => 6,
            'questionMarkerId' => 1,
            'userId'           => 7,
            'status'           => 'right'
        );

        $updatedResult = $this->getQuestionMarkerResultService()->updateQuestionMarkerResult($savedResult['id'], $result);

        $this->assertNotNull($updatedResult['updatedTime']);
        $this->assertEquals($result['markerId'], $updatedResult['markerId']);
        $this->assertEquals($result['questionMarkerId'], $updatedResult['questionMarkerId']);
        $this->assertEquals($result['userId'], $updatedResult['userId']);
        $this->assertEquals($result['status'], $updatedResult['status']);

    }

    protected function getQuestionMarkerResultService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerResultService');
    }

}
