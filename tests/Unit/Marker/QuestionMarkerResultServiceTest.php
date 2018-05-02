<?php

namespace Tests\Unit\Marker;

use Biz\BaseTestCase;
use Biz\Marker\Service\QuestionMarkerResultService;

class QuestionMarkerResultServiceTest extends BaseTestCase
{
    public function testAddQuestionMarkerResult()
    {
        $result = array(
            'markerId' => 1,
            'questionMarkerId' => 2,
            'taskId' => 1,
            'userId' => 1,
            'status' => 'none',
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);
        $this->assertNotNull($savedResult);
        $this->assertEquals($result['markerId'], $savedResult['markerId']);
        $this->assertEquals($result['questionMarkerId'], $savedResult['questionMarkerId']);
        $this->assertEquals($result['userId'], $savedResult['userId']);
        $this->assertEquals($result['status'], $savedResult['status']);
    }

    public function testGetQuestionMarkerResult()
    {
        $result = array(
            'markerId' => 1,
            'questionMarkerId' => 2,
            'taskId' => 1,
            'userId' => 1,
            'status' => 'none',
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);
        $result = $this->getQuestionMarkerResultService()->getQuestionMarkerResult($savedResult['id']);
        $this->assertEquals($savedResult, $result);
    }

    public function testFinishQuestionMarker()
    {
        $question = array(
            'type' => 'single_choice',
            'stem' => 'question.',
            'difficulty' => 'normal',
            'answer' => array('answer'),
            'target' => 'course-1',
            'choices' => array('爱', '测', '额', '恶'),
            'uncertain' => 0,
            'analysis' => '',
            'score' => '2',
            'submission' => 'submit',
            'parentId' => 0,
            'copyId' => 1,
            'courseSetId' => 1,
        );
        $fields = array(
            'second' => 30,
            'questionId' => 1,
        );

        $question = $this->getQuestionService()->create($question);
        $this->getMarkerService()->addMarker(1, $fields);
        $questionMarker = $this->getQuestionMarkerService()->addQuestionMarker($question['id'], 1, 1);

        $savedResult = $this->getQuestionMarkerResultService()->finishQuestionMarker($questionMarker['id'], array(
            'answer' => array('answer'),
            'taskId' => 1,
            'userId' => 1,
        ));
        $this->assertEquals($questionMarker['id'], $savedResult['questionMarkerId']);
    }

    public function testFindByUserIdAndQuestionMarkerId()
    {
        $result = array(
            'markerId' => 1,
            'questionMarkerId' => 2,
            'taskId' => 1,
            'userId' => 1,
            'status' => 'none',
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);
        $results = $this->getQuestionMarkerResultService()->findByUserIdAndQuestionMarkerId(1, 2);

        $this->assertContains($savedResult, $results);
    }

    public function testFindResultByIds()
    {
        $result = array(
            'markerId' => 1,
            'questionMarkerId' => 2,
            'taskId' => 1,
            'userId' => 1,
            'status' => 'none',
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);
        $results = $this->getQuestionMarkerResultService()->findResultsByIds(array($savedResult['id']));

        $this->assertContains($savedResult, $results);
    }

    public function testUpdateQuestionMarkerResult()
    {
        $result = array(
            'markerId' => 3,
            'questionMarkerId' => 4,
            'taskId' => 1,
            'userId' => 6,
            'status' => 'noAnswer',
        );
        $savedResult = $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result);

        $result = array(
            'markerId' => 6,
            'questionMarkerId' => 1,
            'taskId' => 1,
            'userId' => 7,
            'status' => 'right',
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
        $jim = 3;
        $tony = 4;
        $andy = 10;
        $markerId = 3;

        $result1 = array(
            'markerId' => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'taskId' => 1,
            'userId' => $jim,
            'status' => 'noAnswer',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result1);

        $result2 = array(
            'markerId' => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'taskId' => 1,
            'userId' => $tony,
            'status' => 'wrong',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result2);

        $result3 = array(
            'markerId' => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'taskId' => 1,
            'userId' => $andy,
            'status' => 'right',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result3);

        $this->getQuestionMarkerResultService()->deleteByQuestionMarkerId($questionMarkerId);
        $jimResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($jim, $markerId);
        $tonyResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($tony, $markerId);
        $andyResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($andy, $markerId);

        $this->assertTrue(empty($jimResult));
        $this->assertTrue(empty($tonyResult));
        $this->assertTrue(empty($andyResult));
    }

    public function testFindByUserIdAndMarkerId()
    {
        $questionMarkerId = 4;
        $jim = 3;
        $markerId = 3;

        $result1 = array(
            'markerId' => $markerId,
            'questionMarkerId' => $questionMarkerId,
            'taskId' => 1,
            'userId' => $jim,
            'status' => 'noAnswer',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result1);

        $jimResult = $this->getQuestionMarkerResultService()->findByUserIdAndMarkerId($jim, $markerId);

        $this->assertTrue(!empty($jimResult));
        $this->assertEquals(1, count($jimResult));
    }

    public function testFindByLessonIdAndQuestionMarkerId()
    {
        $result1 = array(
            'markerId' => 1,
            'questionMarkerId' => 1,
            'taskId' => 1,
            'userId' => 1,
            'status' => 'noAnswer',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result1);

        $result2 = array(
            'markerId' => 1,
            'questionMarkerId' => 1,
            'taskId' => 1,
            'userId' => 2,
            'status' => 'noAnswer',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result2);

        $result3 = array(
            'markerId' => 1,
            'questionMarkerId' => 2,
            'taskId' => 2,
            'userId' => 1,
            'status' => 'noAnswer',
        );
        $this->getQuestionMarkerResultService()->addQuestionMarkerResult($result3);

        $result = $this->getQuestionMarkerResultService()->findByTaskIdAndQuestionMarkerId(1, 1);
        $this->assertCount(2, $result);
    }

    /**
     * @return QuestionMarkerResultService
     */
    protected function getQuestionMarkerResultService()
    {
        return $this->createService('Marker:QuestionMarkerResultService');
    }

    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker:QuestionMarkerService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }
}
