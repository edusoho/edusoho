<?php

namespace Tests\Unit\MultiClass\Service;

use Biz\BaseTestCase;
use Biz\MultiClass\Dao\MultiClassProductDao;
use Biz\MultiClass\Service\MultiClassGroupService;

class MultiClassGroupServiceTest extends BaseTestCase
{
    public function testFindGroupsByIds()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByIds([1, 2, 3]);

        $this->assertEquals(3, count($result));
    }

    public function testFindGroupsByMultiClassId()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByMultiClassId(1);

        $this->assertEquals(4, count($result));
    }

    public function testGetMultiClassGroup()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals('分组1', $result['name']);
    }

    public function testCreateMultiClassGroups()
    {
        $multiClass = [
            'id' => 1,
            'type' => 'group',
            'group_limit_num' => 3,
        ];
        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'findGroupUserIdsByCourseIdAndRoles',
                'runTimes' => 1,
                'withParams' => [3, ['student', 'assistant']],
                'returnValue' => [
                    'student' => [
                        ['userId' => 1],
                        ['userId' => 2],
                        ['userId' => 3],
                        ['userId' => 4],
                        ['userId' => 5],
                        ['userId' => 6],
                        ['userId' => 7],
                    ],
                ],
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassGroupDao', [
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'withParams' => [[
                    'student_num' => 3,
                    'name' => '分组1',
                    'seq' => 1,
                    'course_id' => 3,
                    'multi_class_id' => 1,
                    'assistant_id' => 0,
                ]],
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'withParams' => [[
                    'student_num' => 3,
                    'name' => '分组2',
                    'seq' => 2,
                    'course_id' => 3,
                    'multi_class_id' => 1,
                    'assistant_id' => 0,
                ]],
                'returnValue' => ['id' => 2],
            ],
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'withParams' => [[
                    'student_num' => 1,
                    'name' => '分组3',
                    'seq' => 3,
                    'course_id' => 3,
                    'multi_class_id' => 1,
                    'assistant_id' => 0,
                ]],
                'returnValue' => ['id' => 3],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentDao', [
            [
                'functionName' => 'batchCreate',
                'runTimes' => 1,
                'withParams' => [[
                    [
                        'studentId' => 1,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 1,
                    ],
                    [
                        'studentId' => 2,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 1,
                    ],
                    [
                        'studentId' => 3,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 1,
                    ],
                ]],
            ],
            [
                'functionName' => 'batchCreate',
                'runTimes' => 1,
                'withParams' => [[
                    [
                        'studentId' => 4,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 2,
                    ],
                    [
                        'studentId' => 5,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 2,
                    ],
                    [
                        'studentId' => 6,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 2,
                    ],
                ]],
            ],
            [
                'functionName' => 'batchCreate',
                'runTimes' => 1,
                'withParams' => [[
                    [
                        'studentId' => 7,
                        'courseId' => 3,
                        'multiClassId' => 1,
                        'group_id' => 3,
                    ],
                ]],
            ],
        ]);
        $this->biz['@noEvent'] = true;

        $result = $this->getMultiClassGroupService()->createMultiClassGroups(3, $multiClass);

        $this->assertTrue($result);
    }

    public function testFindGroupsByCourseId()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->findGroupsByCourseId(1);

        $this->assertEquals(4, count($result));
    }

    public function testGetLiveGroupByUserIdAndCourseId()
    {
        $this->mockBiz('MultiClass:MultiClassService', [
            [
                'functionName' => 'getMultiClassByCourseId',
                'runTimes' => 1,
                'withParams' => [2],
                'returnValue' => ['id' => 3],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentService', [
            [
                'functionName' => 'getByStudentIdAndMultiClassId',
                'runTimes' => 1,
                'withParams' => [1, 3],
                'returnValue' => ['group_id' => 5],
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassLiveGroupDao', [
            [
                'functionName' => 'getByGroupId',
                'runTimes' => 1,
                'withParams' => [5],
                'returnValue' => ['live_code' => 'test'],
            ],
        ]);
        $result = $this->getMultiClassGroupService()->getLiveGroupByUserIdAndCourseId(1, 2, 1);

        $this->assertEquals('test', $result['live_code']);
    }

    public function testCreateLiveGroup()
    {
        $result = $this->createMultiClassLiveGroup();

        $this->assertEquals(1, $result['id']);
    }

    public function testBatchCreateLiveGroups()
    {
        $result = $this->batchCreateGroup();

        $this->assertTrue($result);
    }

    public function testSetGroupNewStudent()
    {
        $multiClass = [
            'id' => 1,
            'type' => 'group',
            'group_limit_num' => 3,
            'courseId' => 3,
        ];
        $this->mockBiz('MultiClass:MultiClassGroupDao', [
            [
                'functionName' => 'getNoFullGroup',
                'runTimes' => 1,
                'withParams' => [1, 3],
                'returnValue' => [
                    'id' => 2,
                    'student_num' => 1,
                ],
            ],
            [
                'functionName' => 'update',
                'runTimes' => 1,
                'withParams' => [2, ['student_num' => 2]],
                'returnValue' => [
                    'id' => 2,
                    'assistant_id' => 5,
                ],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentDao', [
            [
                'functionName' => 'create',
                'runTimes' => 1,
                'withParams' => [[
                    'studentId' => 1,
                    'courseId' => 3,
                    'multiClassId' => 1,
                    'group_id' => 2,
                    'assistantId' => 5,
                ]],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentService', [
            [
                'functionName' => 'setGroupAssistantAndStudents',
                'runTimes' => 1,
                'withParams' => [3, 1],
            ],
        ]);
        $result = $this->getMultiClassGroupService()->setGroupNewStudent($multiClass, 1);

        $this->assertTrue($result);
    }

    public function testDeleteMultiClassGroup()
    {
        $this->mockBiz('MultiClass:MultiClassGroupDao', [
            [
                'functionName' => 'delete',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => 1,
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassLiveGroupDao', [
            [
                'functionName' => 'getByGroupId',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => [
                    'id' => 3,
                ],
            ],
            [
                'functionName' => 'delete',
                'runTimes' => 1,
                'withParams' => [3],
            ],
        ]);
        $this->biz['@noEvent'] = true;

        $result = $this->getMultiClassGroupService()->deleteMultiClassGroup(1);

        $this->assertEquals(1, $result);
    }

    public function testSortMultiClassGroup()
    {
        $this->batchCreateGroup();

        $this->getMultiClassGroupService()->sortMultiClassGroup(1);

        $group = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals(1, $group['seq']);
    }

    public function testUpdateMultiClassGroup()
    {
        $this->batchCreateGroup();

        $this->getMultiClassGroupService()->updateMultiClassGroup(1, ['seq' => 2]);

        $group = $this->getMultiClassGroupService()->getMultiClassGroup(1);

        $this->assertEquals(2, $group['seq']);
    }

    public function testGetLatestGroup()
    {
        $this->batchCreateGroup();

        $result = $this->getMultiClassGroupService()->getLatestGroup(1);

        $this->assertEquals(4, $result['id']);
    }

    public function testBatchUpdateGroupAssistant()
    {
        $this->mockBiz('MultiClass:MultiClassGroupDao', [
            [
                'functionName' => 'findByIds',
                'runTimes' => 1,
                'withParams' => [[1]],
                'returnValue' => [1 => ['id' => 1, 'seq' => 1]],
            ],
            [
                'functionName' => 'batchUpdate',
                'runTimes' => 1,
                'withParams' => [[1], [['id' => 1, 'assistant_id' => 1]]],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentService', [
            [
                'functionName' => 'findAssistantStudentsByGroupIds',
                'runTimes' => 1,
                'withParams' => [[1]],
                'returnValue' => [['id' => 3, 'studentId' => 5, 'group_id' => 1]],
            ],
        ]);
        $this->mockBiz('Assistant:AssistantStudentDao', [
            [
                'functionName' => 'batchUpdate',
                'runTimes' => 1,
                'withParams' => [[3], [['id' => 3, 'assistantId' => 1]]],
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassService', [
            [
                'functionName' => 'getMultiClass',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => ['title' => 'test'],
            ],
        ]);
        $this->mockBiz('User:UserService', [
            [
                'functionName' => 'getUser',
                'runTimes' => 1,
                'withParams' => [1],
                'returnValue' => ['nickname' => 'test'],
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassRecordService', [
            [
                'functionName' => 'makeSign',
                'runTimes' => 1,
                'returnValue' => 'test',
            ],
        ]);
        $this->mockBiz('MultiClass:MultiClassRecordDao', [
            [
                'functionName' => 'batchCreate',
                'runTimes' => 1,
                'withParams' => [[
                    [
                        'user_id' => 5,
                        'assistant_id' => 1,
                        'multi_class_id' => 1,
                        'data' => ['title' => '加入班课', 'content' => '加入班课(test)的分组1, 分配助教(test)'],
                        'sign' => 'test',
                        'is_push' => 0,
                    ],
                ]],
            ],
        ]);

        $result = $this->getMultiClassGroupService()->batchUpdateGroupAssistant(1, [1], 1);

        $this->assertTrue($result);
    }

    public function createMultiClassLiveGroup()
    {
        $fields = [
            'id' => '1',
            'group_id' => 1,
            'live_code' => 1,
            'live_id' => 1,
            'created_time' => time(),
        ];

        return $this->getMulticlassLiveGroupDao()->create($fields);
    }

    protected function batchCreateGroup()
    {
        return $this->getMulticlassGroupDao()->batchCreate([
            [
                'id' => 1,
                'name' => '分组1',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 1,
            ],
            [
                'id' => 2,
                'name' => '分组2',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 2,
            ],
            [
                'id' => 3,
                'name' => '分组3',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 3,
            ],
            [
                'id' => 4,
                'name' => '分组4',
                'assistant_id' => 1,
                'multi_class_id' => 1,
                'course_id' => 1,
                'student_num' => 1,
                'seq' => 4,
            ],
        ]);
    }

    protected function getMultiClassLiveGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassLiveGroupDao');
    }

    /**
     * @return MultiClassGroupService
     */
    protected function getMultiClassGroupService()
    {
        return $this->createService('MultiClass:MultiClassGroupService');
    }

    /**
     * @return MultiClassProductDao
     */
    protected function getMulticlassGroupDao()
    {
        return $this->createDao('MultiClass:MultiClassGroupDao');
    }
}
