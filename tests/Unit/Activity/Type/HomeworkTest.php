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

        $results = $type->find([1, 2]);

        $this->assertEquals(1, count($results));
        $this->assertEquals(1, $results[0]['id']);
    }

    public function testCreate()
    {
        $this->_mockTestpaper();

        $type = $this->getActivityConfig(self::TYPE);

        $fields = [
            'title' => 'homework activity',
            'description' => '',
            'questionIds' => [1, 2],
        ];
        $activity = $type->create($fields);

        $this->assertEquals(2, $activity['id']);
    }

    public function testCopy()
    {
        $this->_mockTestpaper();

        $type = $this->getActivityConfig(self::TYPE);

        $copy = $type->copy(['mediaId' => 1], []);
        $this->assertEquals(2, $copy['id']);
    }

    public function testSync()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockItemService();
        $fields = $this->mockField();

        $homework = $type->create($fields);
        $homework2 = $type->create(array_merge($fields, ['name' => 'homework2 name', 'description' => 'homework2 description']));

        $type->sync(['mediaId' => $homework['id']], ['mediaId' => $homework2['id']]);

        $syncHomework2 = $type->get($homework2['id']);

        $this->assertEquals($homework['name'], $syncHomework2['name']);
        $this->assertEquals($homework['description'], $syncHomework2['description']);
    }

    /**
     * @expectedException \Biz\Testpaper\TestpaperException
     * @expectedExceptionMessage exception.testpaper.not_found
     */
    public function testUpdate()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockItemService();
        $fields = $this->mockField();
        $activity = $type->create($fields);

        $update = ['title' => 'homework update name', 'description' => 'homework update description'];

        $updated = $type->update($activity['id'], $update, []);
        $activity = $type->get($updated['id']);

        $this->assertEquals($update['title'], $activity['name']);

        $type->update(123, $update, []);
    }

    public function testDelete()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockItemService();
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

        $this->mockBiz('Testpaper:TestpaperService', [
            [
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => ['mediaId' => 1],
            ],
            [
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => [],
            ],
        ]);

        $result = $type->isFinished(1);
        $this->assertFalse($result);
    }

    public function testIsFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockActivityService();

        $this->mockBiz('Testpaper:TestpaperService', [
            [
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => ['mediaId' => 1, 'passedCondition' => ['type' => 'submit']],
            ],
            [
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => ['status' => 'finished'],
            ],
        ]);

        $result = $type->isFinished(1);
        $this->assertTrue($result);
    }

    public function testUnFinished()
    {
        $type = $this->getActivityConfig(self::TYPE);

        $this->_mockActivityService();

        $this->mockBiz('Testpaper:TestpaperService', [
            [
                'functionName' => 'getTestpaperByIdAndType',
                'returnValue' => ['mediaId' => 1, 'passedCondition' => ['type' => 'submit']],
            ],
            [
                'functionName' => 'getUserLatelyResultByTestId',
                'returnValue' => ['status' => 'doing'],
            ],
        ]);

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
        $this->mockBiz('ItemBank:Assessment:AssessmentService', [
            [
                'functionName' => 'getAssessment',
                'returnValue' => [
                    'id' => 1,
                    'name' => 'homework name',
                    'description' => 'homework description',
                    'itemCount' => 2,
                ],
            ],
            [
                'functionName' => 'findAssessmentsByIds',
                'returnValue' => [['id' => 1]],
            ],
            [
                'functionName' => 'createAssessment',
                'returnValue' => ['id' => 2, 'name' => 'testpaper name'],
            ],
            [
                'functionName' => 'openAssessment',
                'returnValue' => ['id' => 2, 'name' => 'testpaper name'],
            ],
            [
                'functionName' => 'updateTestpaper',
                'returnValue' => [],
            ],
            [
                'functionName' => 'findItemsByTestId',
                'returnValue' => [['questionId' => 1]],
            ],
        ]);
    }

    private function _mockItemService()
    {
        $this->mockBiz('ItemBank:Item:ItemService', [
            [
                'functionName' => 'findItemsByIds',
                'returnValue' => [
                    [
                        'id' => 1,
                        'bank_id' => 1,
                        'questions' => [],
                    ]
                ],
            ]
        ]);
    }

    private function _mockActivityService()
    {
        $this->mockBiz('Activity:ActivityService', [
            [
                'functionName' => 'getActivity',
                'returnValue' => ['id' => 1, 'mediaId' => 1, 'fromCourseId' => 1, 'finishType' => 'submit', 'finishData' => ''],
            ],
        ]);
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
        return [
            'title' => 'homework',
            'description' => 'homework description',
            'questionIds' => [1],
        ];
    }
}
