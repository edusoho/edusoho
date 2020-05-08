<?php

namespace Tests\Unit\Activity\Type;

class TestpaperTest extends BaseTypeTestCase
{
    const TYPE = 'testpaper';

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $result = $type->get(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals(1, $result['testpaper']['id']);
        $this->assertEquals(1, $result['answerScene']['id']);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $results = $type->find(array(1, 2));

        $this->assertEquals(1, count($results));
        $this->assertEquals(1, $results[0]['id']);
    }

    public function testCreate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $activity = $type->create(array(
            'testpaperId' => 2,
            'checkType' => '',
            'requireCredit' => '',
            'finishCondition' => 2,
        ));

        $this->assertEquals(2, $activity['mediaId']);
    }

    public function testCopy()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mockTestpaperActivity = $this->_mockTestpaperActivity();
        $result = $type->copy(array('mediaType' => 'homework', array()));
        $this->assertNull($result);

        $copy = $type->copy(array('mediaType' => 'testpaper', 'mediaId' => 1), array());
        $this->assertEquals(1, $copy['mediaId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mockTestpaperActivity = $this->_mockTestpaperActivity();

        $mockTestpaperActivity2 = $this->getTestpaperActivityDao()->create(array(
            'id' => 2,
            'answerSceneId' => 2,
            'mediaId' => 1,
        ));

        $syncedActivity = $type->sync(array('mediaId' => $mockTestpaperActivity['id']), array('mediaId' => $mockTestpaperActivity2['id'], 'fromCourseSetId' => 2));

        $this->assertEquals($syncedActivity['answerSceneId'], 2);
    }

    /**
     * @expectedException \Biz\Activity\ActivityException
     * @expectedExceptionMessage exception.activity.not_found
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $update = array('name' => 'test');

        $result = $type->update(1, $update, array());
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mockTestpaperActivity = $this->_mockTestpaperActivity();

        $type->delete($mockTestpaperActivity['id']);
        $result = $type->get($mockTestpaperActivity['id']);

        $this->assertNull($result);
    }

    public function testIsFinishedBySubmit()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('finishType' => 'submit', 'ext' => array()),
            ),
        ));

        $result = $type->isFinished(1);

        $this->assertTrue($result);
    }

    public function testIsFinishedByScore()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'finished', 'score' => 20),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array(
                    'id' => 1,
                    'mediaId' => $activity['id'],
                    'fromCourseId' => 1,
                    'ext' => array(
                        'id' => 1,
                        'testpaper' => array(
                            'id' => 2,
                            'score' => 10,
                        ),
                    ),
                    'finishType' => 'score',
                    'finishData' => '0.8',
                ),
            ),
        ));

        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    public function testUnFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaperActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('finishType' => 'doing', 'ext' => array()),
            ),
        ));

        $result = $type->isFinished(1);

        $this->assertFalse($result);
    }

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = $type->getListener('activity.created');

        $this->assertInstanceOf('Biz\Activity\Listener\TestpaperActivityCreateListener', $result);
    }

    private function _mockTestpaperActivity()
    {
        $this->mockBiz('ItemBank:Assessment:AssessmentService', array(
            array(
                'functionName' => 'getAssessment',
                'returnValue' => array('id' => 1),
            ),
        ));

        $this->mockBiz('ItemBank:Answer:AnswerSceneService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'create',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1),
            ),
        ));

        return $this->getTestpaperActivityDao()->create(array(
            'id' => 1,
            'answerSceneId' => 1,
            'mediaId' => 1,
        ));
    }

    protected function getTestpaperActivityDao()
    {
        return $this->getBiz()->dao('Activity:TestpaperActivityDao');
    }
}
