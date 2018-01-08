<?php

namespace Tests\Unit\Activity\Type;

class TestpaperTest extends BaseTypeTestCase
{
    const TYPE = 'testpaper';

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $result = $type->get(1);

        $this->assertArrayEquals($activity, $result);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields1 = $this->mockField(1);
        $activity1 = $type->create($fields1);

        $fields2 = $this->mockField(2);
        $activity2 = $type->create($fields2);

        $fields3 = $this->mockField(3);
        $activity3 = $type->create($fields3);

        $results = $type->find(array($activity1['id'], $activity2['id']));

        $this->assertEquals(2, count($results));
        $this->assertEquals(1, $results[0]['id']);
    }

    public function testCreate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $mediaId = 1;
        $fields = $this->mockField($mediaId);
        $activity = $type->create($fields);

        $this->assertEquals(1, $activity['mediaId']);
    }

    public function testCopy()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $result = $type->copy(array('mediaType' => 'homework', array()));
        $this->assertNull($result);

        $fields1 = $this->mockField(1);
        $activity1 = $type->create($fields1);

        $copy = $type->copy(array('mediaType' => 'testpaper', 'mediaId' => 1), array('testId' => 2));
        $this->assertEquals(2, $copy['mediaId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $copyFields = $this->mockField(2);
        $copyActivity = $type->create($copyFields);
        $this->assertEquals($copyFields['mediaId'], $copyActivity['mediaId']);
        $this->assertNotEquals($activity['mediaId'], $copyActivity['mediaId']);

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByCopyIdAndCourseSetId',
                'returnValue' => array('id' => 1),
            ),
        ));

        $syncedActivity = $type->sync(array('mediaId' => $activity['id']), array('mediaId' => $copyActivity['id'], 'fromCourseSetId' => 2));

        $result = $type->get($copyActivity['id']);

        $this->assertEquals($activity['mediaId'], $result['mediaId']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @expectedExceptionMessage 教学活动不存在
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $update = array('mediaId' => 2, 'length' => 50, 'doTimes' => 0);

        $result = $type->update($activity['id'], $update, array());

        $activity = $type->get($result['id']);
        $this->assertEquals($update['length'], $result['limitedTime']);
        $this->assertEquals($update['mediaId'], $result['mediaId']);

        $type->update(123, $update, array());
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $this->assertNotNull($activity);

        $type->delete($activity['id']);
        $result = $type->get($activity['id']);

        $this->assertNull($result);
    }

    public function testIsFinishedEmpty()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'mediaId' => $activity['id'], 'fromCourseId' => 1),
            ),
        ));
        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array(),
            ),
        ));

        $result = $type->isFinished(1);
        $this->assertFalse($result);
    }

    public function testIsFinishedBySubmit()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'mediaId' => $activity['id'], 'fromCourseId' => 1),
            ),
        ));
        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    public function testIsFinishedByScore()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = array_merge($this->mockField(1), array('finishCondition' => array('type' => 'score', 'finishScore' => 5, 'condition' => 'score')));
        $activity = $type->create($fields);

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'mediaId' => $activity['id'], 'fromCourseId' => 1),
            ),
        ));
        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('status' => 'finished', 'score' => 8),
            ),
        ));

        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    public function testUnFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField(1);
        $activity = $type->create($fields);

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'mediaId' => $activity['id'], 'fromCourseId' => 1),
            ),
        ));
        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('status' => 'doing'),
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

    /**
     * [mockField description]
     *
     * @param [type] $mediaId [description]
     *
     * @return [type] [description]
     */
    private function mockField($mediaId)
    {
        return array(
            'mediaId' => $mediaId,
            'doTimes' => 1,
            'redoInterval' => 0,
            'length' => 30,
            'checkType' => 'score',
            'finishCondition' => array('type' => 'submit', 'finishScore' => 0),
            'condition' => 'submit',
            'finishScore' => 5,
        );
    }
}
