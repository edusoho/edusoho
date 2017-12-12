<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class ExerciseTest extends BaseTypeTestCase
{
    const TYPE = 'homework';

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
                    'returnValue' => array('id' => 22, 'name' => 'name', 'type' => 'homework'),
                    'withParams' => array(22, 'homework'),
                ),
            )
        );
        $result = $type->get(22);
        $this->assertEquals(array('id' => 22, 'name' => 'name', 'type' => 'homework'), $result);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'findTestpapersByIdsAndType',
                    'returnValue' => array(array('id' => 22, 'name' => 'name', 'type' => 'homework')),
                    'withParams' => array(array(22), 'homework'),
                ),
            )
        );
        $result = $type->find(array(22));
        $this->assertEquals(array(array('id' => 22, 'name' => 'name', 'type' => 'homework')), $result);
    }

    public function testCreate()
    {
        $fields = $this->getFields();
        $filterFields = $this->getFields();
        $filterFields['name'] = $fields['title'];
        $filterFields['passedCondition']['type'] = $filterFields['finishCondition'];
        $filterFields['lessonId'] = 0;
        $type = $this->getActivityConfig(self::TYPE);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'buildTestpaper',
                    'returnValue' => array('id' => 22, 'name' => 'title'),
                    'withParams' => array($filterFields, 'homework'),
                ),
            )
        );
        $result = $type->create($fields);
        $this->assertEquals(array('id' => 22, 'name' => 'title'), $result);
    }

    public function testCopy()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $filterFields = $this->getFields();
        $filterFields['name'] = $filterFields['title'];
        $filterFields['passedCondition']['type'] = $filterFields['finishCondition'];
        $filterFields['lessonId'] = 0;
        $filterFields['copyId'] = 1;
        $homework = $this->getFields();
        $homework['passedCondition']['type'] = $homework['finishCondition'];
        $homework['id'] = 1;
        $homework['name'] = 'title';
        $homework['questionId'] = '';
        $config = array('newActivity' => array('fromCourseId' => 11, 'fromCourseSetId' => 11), 'isCopy' => 1);
        $this->mockBiz(
            'Testpaper:TestpaperService',
            array(
                array(
                    'functionName' => 'getTestpaperByIdAndType',
                    'returnValue' => $homework,
                    'withParams' => array(1, 'homework'),
                ),
                array(
                    'functionName' => 'findItemsByTestId',
                    'returnValue' => array('id' => 1, 'name' => 'title'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'buildTestpaper',
                    'returnValue' => array('id' => 1, 'name' => 'title'),
                    'withParams' => array($filterFields, 'homework'),
                ),
            )
        );
        $result = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(1, $result['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $homework = $type->create($this->getFields());
        $result = $type->sync(array('mediaId' => 1), array('mediaId' => 1));
        $this->assertEquals(1, $result['id']);
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
    public function testUpdateWithEmptyHomework()
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
                    'withParams' => array(22, 'homework'),
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array(),
                    'withParams' => array(1, 22, 22, 22, 'homework'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array('status' => 'finished'),
                    'withParams' => array(1, 22, 22, 22, 'homework'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getUserLatelyResultByTestId',
                    'returnValue' => array('status' => 'processing'),
                    'withParams' => array(1, 22, 22, 22, 'homework'),
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

    /**
     * @expectedException \AppBundle\Common\Exception\InvalidArgumentException
     */
    public function testFilterFieldsWithoutFinishCondition()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $fields = $this->getFields();
        unset($fields['finishCondition']);
        $result = ReflectionUtils::invokeMethod($type, 'filterFields', array($fields));
    }

    public function testFindQuestionsByCopydIdsAndCourseSetId()
    {
        $type = $this->getActivityConfig(self::TYPE);
        $conditions = array(
            'copyIds' => array(2),
            'courseSetId' => 2,
        );
        $this->mockBiz(
            'Question:QuestionService',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array('id' => 2, 'courseSetId' => 2),
                    'withParams' => array($conditions, array(), 0, PHP_INT_MAX),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($type, 'findQuestionsByCopydIdsAndCourseSetId', array(
            array(2), 
            2
        ));
        $this->assertEquals(array('id' => 2, 'courseSetId' => 2), $result);
    }

    protected function getFields()
    {
        return array(
            'title' => 'title',
            'description' => 'description',
            'questionIds' => array(),
            'finishCondition' => 'finishied',
            'passedCondition' => array(),
            'fromCourseId' => 11,
            'fromCourseSetId' => 11,
            'copyId' => 11,
            'courseSetId' => 11,
            'courseId' => 11,
        );
    }
}