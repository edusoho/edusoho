<?php

namespace Tests\Unit\Activity\Type;

class ExerciseTest extends BaseTypeTestCase
{
    const TYPE = 'exercise';

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mockExerciseActivity = $this->_mockExerciseActivity();

        $exerciseActivity = $type->get(1);

        $this->assertArrayEquals($exerciseActivity, $mockExerciseActivity);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockExerciseActivity();

        $result = $type->find(array(1));

        $this->assertEquals(1, count($result));
    }

    public function testCreate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = array(
            'title' => 'exercise activity',
            'questionTypes' => array('single_choice'),
            'range' => array('bankId' => 1),
            'itemCount' => 1,
        );
        $activity = $type->create($fields);

        $this->assertEquals(1, $activity['id']);
    }

    public function testCopy()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $mockExerciseActivity = $this->_mockExerciseActivity();

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1,
                'title' => 'test',
            ),
            'isSync' => 1,
            'isCopy' => 0,
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mockExerciseActivity = $this->_mockExerciseActivity();
        $mockExerciseActivity2 = $this->getExerciseActivityDao()->create(array(
            'id' => 2,
            'answerSceneId' => 1,
            'drawCondition' => array(
                'range' => array(
                    'question_bank_id' => 1,
                    'bank_id' => 1,
                    'category_ids' => array(),
                    'difficulty' => '',
                ),
                'section' => array(
                    'conditions' => array(
                        'item_types' => array('single_choice'),
                    ),
                    'item_count' => 2,
                    'name' => '练习题目',
                ),
            ),
        ));

        $this->mockBiz('ItemBank:Answer:AnswerSceneService', array(
            array(
                'functionName' => 'update',
                'returnValue' => array(),
            ),
        ));

        $type->sync(array('mediaId' => $mockExerciseActivity['id'], 'title' => 'test'), array('title' => 'test', 'mediaId' => $mockExerciseActivity2['id']));

        $activity = $type->get($mockExerciseActivity2['id']);

        $this->assertEquals('2', $mockExerciseActivity2['id']);
    }

    /**
     * @expectedException \Biz\Activity\ActivityException
     * @expectedExceptionMessage exception.activity.not_found
     */
    public function testUpdate_returnException()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $field = array('title' => 'test2');
        $result = $type->update(1, $field, array());
    }

    public function testUpdate()
    {
        $this->mockBiz('ItemBank:Answer:AnswerSceneService', array(
            array(
                'functionName' => 'update',
                'returnValue' => array(),
            ),
        ));

        $type = $this->getActivityConfig(self::TYPE);

        $mockExerciseActivity = $this->_mockExerciseActivity();

        $field = array(
            'title' => 'test2',
            'range' => array('bankId' => 1),
            'itemCount' => 1,
            'questionTypes' => array(),
        );
        $result = $type->update(1, $field, array());

        $this->assertEquals($result['id'], '1');
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $mockExerciseActivity = $this->_mockExerciseActivity();
        $type->delete($mockExerciseActivity['id']);
        $result = $type->get($mockExerciseActivity['id']);
        $this->assertNull($result);
    }

    public function testIsFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockExerciseActivity();

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

        $this->_mockExerciseActivity();

        $this->mockBiz('ItemBank:Answer:AnswerRecordService', array(
            array(
                'functionName' => 'getLatestAnswerRecordByAnswerSceneIdAndUserId',
                'returnValue' => array('status' => 'doing'),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('finishType' => 'submit', 'ext' => array('answerSceneId' => 1)),
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

    private function _mockExerciseActivity()
    {
        return $this->getExerciseActivityDao()->create(array(
            'id' => 1,
            'answerSceneId' => 1,
            'drawCondition' => array(
                'range' => array(
                    'question_bank_id' => 1,
                    'bank_id' => 1,
                    'category_ids' => array(),
                    'difficulty' => '',
                ),
                'section' => array(
                    'conditions' => array(
                        'item_types' => array('single_choice'),
                    ),
                    'item_count' => 2,
                    'name' => '练习题目',
                ),
            ),
        ));
    }

    protected function getExerciseActivityDao()
    {
        return $this->getBiz()->dao('Activity:ExerciseActivityDao');
    }
}
