<?php

namespace Tests\Unit\Activity\Type;

class HomeworkTest extends BaseTypeTestCase
{
    const TYPE = 'homework';

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

        $results = $type->find(array(1, 2));

        $this->assertEquals(1, count($results));
        $this->assertEquals(1, $results[0]['id']);
    }

    public function testCreate()
    {
        $this->_mockTestpaper();

        $type = $this->getActivityConfig(self::TYPE);

        $fields = array(
            'title' => 'homework activity',
            'questionIds' => array(1, 2),
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishCondition' => 'submit',
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
                'fromCourseSetId' => 1,
            ),
            'isCopy' => 1,
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1,
            ),
            'isCopy' => 0,
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);
    }

    public function testCopyQuestionEmpty()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => array(
                    'id' => 1,
                    'name' => 'homework name',
                    'description' => 'homework description',
                    'itemCount' => 2,
                    'passedCondition' => array('type' => 'submit'),
                ),
            ),
            array(
                'functionName' => 'buildTestpaper',
                'returnValue' => array('id' => 2, 'name' => 'testpaper name'),
            ),
            array(
                'functionName' => 'findItemsByTestId',
                'returnValue' => array(array('id' => 1)),
            ),
        ));

        $config = array(
            'newActivity' => array(
                'fromCourseId' => 1,
                'fromCourseSetId' => 1,
            ),
            'isCopy' => 1,
        );
        $copy = $type->copy(array('mediaId' => 1), $config);
        $this->assertEquals(2, $copy['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $fields = $this->mockField();
        $this->_mockQuestionService();

        $homework = $type->create($fields);
        $homework2 = $type->create(array_merge($fields, array('copyId' => $homework['id'], 'fromCourseId' => 3, 'name' => 'homework2 name', 'description' => 'homework2 description')));

        $syncedActivity = $type->sync(array('mediaId' => $homework['id']), array('mediaId' => $homework2['id']));

        $activity = $type->get($homework2['id']);

        $this->assertEquals($homework['name'], $activity['name']);
        $this->assertEquals($homework['description'], $activity['description']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @expectedExceptionMessage 教学活动不存在
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockQuestionService();
        $fields = $this->mockField();
        $activity = $type->create($fields);

        $update = array('title' => 'homework update name', 'finishCondition' => 'submit');

        $updated = $type->update($activity['id'], $update, array());
        $activity = $type->get($updated['id']);

        $this->assertEquals($update['title'], $activity['name']);

        $type->update(123, $update, array());
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockQuestionService();
        $fields = $this->mockField();
        $activity = $type->create($fields);

        $this->assertNotNull($activity);
        $this->assertEquals($fields['title'], $activity['name']);

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
                'returnValue' => array('mediaId' => 1),
            ),
            array(
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => array(),
            ),
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
                'returnValue' => array('mediaId' => 1, 'passedCondition' => array('type' => 'submit')),
            ),
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
                    'name' => 'homework name',
                    'description' => 'homework description',
                    'itemCount' => 2,
                    'passedCondition' => array('type' => 'submit'),
                ),
            ),
            array(
                'functionName' => 'findTestpapersByIdsAndType',
                'returnValue' => array(array('id' => 1)),
            ),
            array(
                'functionName' => 'buildTestpaper',
                'returnValue' => array('id' => 2, 'name' => 'testpaper name'),
            ),
            array(
                'functionName' => 'updateTestpaper',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'findItemsByTestId',
                'returnValue' => array(array('questionId' => 1)),
            ),
        ));
    }

    private function _mockQuestionService()
    {
        $this->mockBiz('Question:QuestionService', array(
            array(
                'functionName' => 'findQuestionsByIds',
                'returnValue' => array(array('id' => 1, 'type' => 'choice', 'parentId' => 0)),
            ),
        ));
    }

    private function _mockActivityService()
    {
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => array('id' => 1, 'mediaId' => 1, 'fromCourseId' => 1, 'finishType' => 'submit', 'finishData' => ''),
            ),
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
            'title' => 'homework',
            'description' => 'homework description',
            'questionIds' => array(1),
            'courseSetId' => 1,
            'courseId' => 2,
            'pattern' => 'questionType',
            'finishCondition' => 'submit',
            'itemCount' => 5,
            'metas' => array(),
            'type' => 'homework',
        );
    }
}
