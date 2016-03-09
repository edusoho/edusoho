<?php

namespace Topxia\Service\Marker\Tests;

use Topxia\Service\Common\BaseTestCase;

class QuestionMarkerResultServiceTest extends BaseTestCase
{
    public function testAddQuestionMarkerResult()
    {
        $result = array(
            'markerId'         => 1,
            'questionMarkerId' => 2,
            'lessonId'        =>1,
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
            'lessonId'        =>1,
            'userId'           => 6,
            'status'           => 'noAnswer'
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);

        $result = array(
            'markerId'         => 6,
            'questionMarkerId' => 1,
            'lessonId'        =>1,
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

    public function testDeleteByQuestionMarkerId()
    {
        $questionMarkerId = 4;
        $jim              = 3;
        $tony             = 4;
        $andy             = 10;
        $markerId         = 3;

        $result1 = array(
            'markerId'         => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'lessonId'        =>1,
            'userId'           => $jim,
            'status'           => 'noAnswer'
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result1);

        $result2 = array(
            'markerId'         => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'lessonId'        =>1,
            'userId'           => $tony,
            'status'           => 'wrong'
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result2);

        $result3 = array(
            'markerId'         => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'lessonId'        =>1,
            'userId'           => $andy,
            'status'           => 'right'
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result3);

        $this->getQuestionMarkerResultService()->deleteByQuestionMarkerId($questionMarkerId);
        $jimResult  = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($jim, $markerId);
        $tonyResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($tony, $markerId);
        $andyResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($andy, $markerId);

        $this->assertTrue(empty($jimResult));
        $this->assertTrue(empty($tonyResult));
        $this->assertTrue(empty($andyResult));
    }

    public function testFindByUserIdAndMarkerId()
    {
        $questionMarkerId = 4;
        $jim              = 3;
        $markerId         = 3;

        $result1 = array(
            'markerId'         => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'lessonId'        =>1,
            'userId'           => $jim,
            'status'           => 'noAnswer'
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result1);

        $jimResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($jim, $markerId);

        $this->assertTrue(!empty($jimResult));
        $this->assertEquals(1, count($jimResult));

    }

    protected function getQuestionMarkerResultService()
    {
        return $this->getServiceKernel()->createService('Marker.QuestionMarkerResultService');
    }

}
