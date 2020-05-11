<?php

namespace Tests\Unit\Activity\Type;

class HomeworkTest extends BaseTypeTestCase
{
    const TYPE = 'homework';

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockHomeworkActivity();

        $result = $type->get(1);

        $this->assertEquals(1, $result['id']);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockHomeworkActivity();

        $results = $type->find([1, 2]);

        $this->assertEquals(1, count($results));
    }

    public function testCreate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = [
            'title' => 'homework activity',
            'description' => '',
            'questionIds' => [1, 2],
        ];

        $this->mockBiz('ItemBank:Assessment:AssessmentService', array(
            array(
                'functionName' => 'createAssessment',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'openAssessment',
                'returnValue' => array('id' => 1),
            ),
        ));

        $activity = $type->create($fields);

        $this->assertEquals(1, $activity['id']);
    }

    public function testCopy()
    {
        $this->_mockHomeworkActivity();

        $this->mockBiz('ItemBank:Assessment:AssessmentService', array(
            array(
                'functionName' => 'createAssessment',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'getAssessment',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'openAssessment',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('ItemBank:Answer:AnswerSceneService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'name' => 'test'),
            ),
            array(
                'functionName' => 'create',
                'returnValue' => array('id' => 2, 'name' => 'test'),
            ),
        ));

        $copy = $this->getActivityConfig(self::TYPE)->copy(['mediaId' => 1], []);
        $this->assertEquals(2, $copy['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $homework = $this->_mockHomeworkActivity();
        $homework2 = $this->getHomeworkActivityDao()->create(array(
            'id' => 2,
            'answerSceneId' => 2,
            'assessmentId' => 2,
        ));

        $this->mockBiz('ItemBank:Assessment:AssessmentService', array(
            array(
                'functionName' => 'updateBasicAssessment',
                'returnValue' => array('id' => 1, 'name' => 'name', 'description' => 'description'),
            ),
            array(
                'functionName' => 'getAssessment',
                'returnValue' => array('id' => 1, 'name' => 'name', 'description' => 'description'),
            ),
        ));

        $type->sync(['mediaId' => $homework['id']], ['mediaId' => $homework2['id']]);

        $syncHomework2 = $type->get($homework2['id']);

        $this->assertEquals($syncHomework2['id'], 2);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $update = ['title' => 'homework update name', 'description' => 'homework update description'];

        $updated = $type->update(1, $update, []);
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $activity = $this->_mockHomeworkActivity();

        $type->delete($activity['id']);

        $result = $type->get($activity['id']);

        $this->assertNull($result);
    }

    public function testIsFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockHomeworkActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('finishType' => 'submit', 'ext' => array('answerSceneId' => 1)),
            ),
        ));

        $result = $type->isFinished(1);

        $this->assertTrue($result);
    }

    public function testUnFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockHomeworkActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('finishType' => 'doing', 'ext' => array('answerSceneId' => 1)),
            ),
        ));

        $result = $type->isFinished(1);

        $this->assertFalse($result);
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $return = $type->getListener('create');

        $this->assertEmpty($return);
    }

    private function _mockHomeworkActivity()
    {
        return $this->getHomeworkActivityDao()->create(array(
            'id' => 1,
            'answerSceneId' => 1,
            'assessmentId' => 1,
        ));
    }

    protected function getHomeworkActivityDao()
    {
        return $this->getBiz()->dao('Activity:HomeworkActivityDao');
    }
}
