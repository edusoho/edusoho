<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class ExerciseTest extends BaseTypeTestCase
{
    const TYPE = 'exercise';

    public function testRegisterListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = ReflectionUtils::invokeMethod($type, 'registerListeners');

        $this->assertEquals(array(), $result);
    }

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperByIdAndType',
                    'returnValue' => array('id' => 11, 'name' => 'name', 'type' => 'exercise'),
                    'withParams' => array(11, 'exercise'),
                ),
            )
        );
        $result = $type->get(11);
        $this->assertEquals(array('id' => 11, 'name' => 'name', 'type' => 'exercise'), $result);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'findTestpapersByIdsAndType',
                    'returnValue' => array(array('id' => 11, 'name' => 'name', 'type' => 'exercise')),
                    'withParams' => array(array(11), 'exercise'),
                ),
            )
        );
        $result = $type->find(array(11));
        $this->assertEquals(array(array('id' => 11, 'name' => 'name', 'type' => 'exercise')), $result);
    }

    public function testCreate()
    {
        $fields = $this->getFields();
        $filterFields = $this->getFields();
        $filterFields['name'] = $fields['title'];
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'buildTestpaper',
                    'returnValue' => array('id' => 11, 'name' => 'title'),
                    'withParams' => array($filterFields, 'exercise'),
                ),
            )
        );
        $result = $type->create($fields);
        $this->assertEquals(array('id' => 11, 'name' => 'title'), $result);
    }

    public function testCopy()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $exercise = $type->create($this->getFields());
        $config = array('newActivity' => array('fromCourseId' => 11, 'fromCourseSetId' => 11), 'isCopy' => 0);
        $result = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(0, $result['metas']['range']['courseId']);

        $config = array('newActivity' => array('fromCourseId' => 11, 'fromCourseSetId' => 11), 'isCopy' => 1);
        $result = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(11, $result['metas']['range']['courseId']);

        $config = array('newActivity' => array('fromCourseId' => 11, 'fromCourseSetId' => 11), 'isSync' => 1, 'isCopy' => 0);
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(array('id' => 12, 'title' => 'title')),
                    'withParams' => array(array('courseId' => 11, 'copyId' => 11,), array(), 0, 1),
                ),
            )
        );
        $result = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(12, $result['metas']['range']['lessonId']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $exercise = $type->create($this->getFields());
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(array('id' => 12, 'title' => 'title')),
                    'withParams' => array(array('courseId' => 11, 'copyId' => 11,), array(), 0, 1),
                ),
            )
        );
        $result = $type->sync(array('mediaId' => 1), array('mediaId' => 1));
        $this->assertEquals(12, $result['metas']['range']['lessonId']);
    }

    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $fields = $this->getFields();
        $fields['title'] = 'test';
        $exercise = $type->create($this->getFields());
        $result = $type->update(1, $fields, array());
        $this->assertEquals('test', $result['name']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateWithEmptyExercise()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $fields = $this->getFields();
        $fields['title'] = 'test';
        $result = $type->update(1, $fields, array());
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'deleteTestpaper',
                    'returnValue' => 1,
                    'withParams' => array(11, true),
                ),
            )
        );
        $result = $type->delete(11);
        $this->assertEquals(1, $result);
    }

    public function testIsFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'getActivity',
                    'returnValue' => array('id' => 22, 'mediaId' => 22, 'fromCourseId' => 22),
                    'withParams' => array(22),
                ),
            )
        );
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperByIdAndType',
                    'returnValue' => array('passedCondition' => array('type' => 'submit')),
                    'withParams' => array(22, 'exercise'),
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array(),
                    'withParams' => array(1, 22, 22, 22, 'exercise'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array('status' => 'finished'),
                    'withParams' => array(1, 22, 22, 22, 'exercise'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array('status' => 'processing'),
                    'withParams' => array(1, 22, 22, 22, 'exercise'),
                    'runTimes' => 1,
                ),
            )
        );
        $result = $type->isFinished(22);
        $this->assertFalse($result);

        $result = $type->isFinished(22);
        $this->assertTrue($result);

        $result = $type->isFinished(22);
        $this->assertFalse($result);
    }

    public function testGetListeners()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = ReflectionUtils::invokeMethod($type, 'getListeners');

        $this->assertEquals(array(), $result);
    }

    public function testFilterFields()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = ReflectionUtils::invokeMethod($type, 'filterFields', array($this->getFields()));

        $this->assertEquals('title', $result['name']);
    }

    public function testGetTaskByCopyIdAndCourseId()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $result = ReflectionUtils::invokeMethod($type, 'getTaskByCopyIdAndCourseId', array(22, 22));

        $this->assertEquals(array(), $result);
    }

    protected function getFields()
    {
        return array(
            'title' => 'title',
            'range' => array('courseId' => 11, 'lessonId' => 11),
            'itemCount' => 10,
            'difficulty' => 'easy',
            'questionTypes' => 'types',
            'finishCondition' => 'finish',
            'passedCondition' => array(),
            'fromCourseId' => 11,
            'fromCourseSetId' => 11,
            'courseSetId' => 11,
            'courseId' => 11,
            'lessonId' => 11,
            'metas' => array(),
            'copyId' => 11,
        );
    }
}