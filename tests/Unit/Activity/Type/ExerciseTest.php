<?php

namespace Tests\Unit\Activity\Type;

use AppBundle\Common\ReflectionUtils;

class ExerciseTest extends BaseTypeTestCase
{
    const TYPE = 'exercise';

    public function testGet()
    {
        $type = $this->getActivityConfig(self::TYPE);
        
        $this->_mockTestpaper();

        $result = $type->get(1);

        $this->assertEquals(1, $result['id']);
    }

    public function testFind()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockTestpaper();

        $results = $type->find(array(1,2));

        $this->assertEquals(1, count($results));
        $this->assertEquals(1, $results[0]['id']);
    }

    public function testCreate()
    {
        $this->_mockTestpaper();

        $type = $this->getActivityConfig(self::TYPE);

        $fields = array(
            'title' => 'exercise activity',
            'questionTypes' => array('single_choice'),
            'fromCourseId' => 1,
            'fromCourseSetId' => 1
        );
        $activity = $type->create($fields);

        $this->assertEquals(2, $activity['id']);
    }

    public function testCopy()
    {
        $this->_mockTestpaper();

        $type = $this->getActivityConfig(self::TYPE);

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1
            ),
            'isSync' => 1,
            'isCopy' => 0
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1
            ),
            'isSync' => 0,
            'isCopy' => 1
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1
            ),
            'isSync' => 0,
            'isCopy' => 0
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField();
        $metas = array('metas' => array('range' => array('courseId' => 1,'lessonId' => 10)));
        
        $exercise = $type->create(array_merge($fields, $metas));
        $exercise2 = $type->create(array_merge($fields, array('copyId' => $exercise['id'],'metas' => array(), 'fromCourseId' => 3)));

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'searchTasks',
                'returnValue' => array(array('id' => 9))
            )
        ));
        $syncedActivity = $type->sync(array('mediaId' => $exercise['id']), array('mediaId' => $exercise2['id']));
        
        $activity = $type->get($exercise2['id']);

        $this->assertArrayEquals(array('range' => array('courseId' => 3, 'lessonId' => 9)), $activity['metas']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @expectedExceptionMessage 教学活动不存在
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField();
        $activity = $type->create($fields);

        $update = array('metas' => array('ranges' => array('courseId' => 1,'lessonId' => 10)));
  
        $updated = $type->update($activity['id'], $update, array());

        $activity = $type->get($updated['id']);
        $this->assertArrayEquals($update['metas'], $activity['metas']);

        $type->update(123, $update, array());
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField();
        $activity = $type->create($fields);

        $this->assertNotNull($activity);

        $type->delete($activity['id']);
        $result = $type->get($activity['id']);

        $this->assertNull($result);
    }

    public function testIsFinishedEmpty()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockActivityService();

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => array('mediaId' => 1)
            ),
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array()
            )
        ));

        $result = $type->isFinished(1);
        $this->assertFalse($result);
    }

    public function testIsFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockActivityService();

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => array('mediaId' => 1, 'passedCondition' => array('type' => 'submit')),
            ),
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('status' => 'finished'),
            ),
        ));

        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    public function testUnFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockActivityService();

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => array('mediaId' => 1)
            ),
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array('status' => 'doing')
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

    private function _mockTestpaper()
    {
        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => array(
                    'id' => 1,
                    'name' => 'exercise name',
                    'itemCount' => 5,
                    'passedCondition' => array('type' => 'submit'),
                    'metas' => array('range' => array('lessonId' =>3, 'courseId' => 1))
                )
            ),
            array(
                'functionName' => 'findTestpapersByIdsAndType',
                'returnValue' => array(array('id'=>1))
            ),
            array(
                'functionName' => 'buildTestpaper',
                'returnValue' => array('id' => 2, 'name' => 'testpaper name')
            ),
            array(
                'functionName' => 'updateTestpaper',
                'returnValue' => array()
            )
        ));
    }

    private function _mockActivityService()
    {
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1,'mediaId' => 1,'fromCourseId' => 1)
            )
        ));
    }

    /**
     * @param string $source
     * @param string $uri
     * @param int    $mediaId
     *
     * @return array
     */
    private function mockField()
    {
        return array(
            'name' => 'exercise',
            'description' => 'exercise description',
            'courseSetId' => 1,
            'courseId' => 2,
            'pattern' => 'questionType',
            'passedCondition' => array('type' => 'submit'),
            'itemCount' => 5,
            'metas' => array(),
            'type' => 'exercise',
        );
    }
}
