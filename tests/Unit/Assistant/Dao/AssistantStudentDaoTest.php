<?php

namespace Tests\Unit\Assistant\Dao;

use Biz\Assistant\Dao\AssistantStudentDao;
use Tests\Unit\Base\BaseDaoTestCase;

class AssistantStudentDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['assistantId' => 1]);
        $expected[] = $this->mockDataObject(['assistantId' => 1, 'multiClassId' => 2]);

        $testCondition = [
            [
                'condition' => ['assistantId' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
        ];

        $this->searchTestUtil($this->getAssistantStudentDao(), $testCondition, $this->getCompareKeys());
    }

    public function testCountMultiClassGroupStudent()
    {
        $this->mockDataObject(['multiClassId' => 1]);

        $res = $this->getAssistantStudentDao()->countMultiClassGroupStudent(1);

        $this->assertEquals(1, $res[0]['assistantId']);
        $this->assertEquals(1, $res[0]['studentNum']);
    }

    public function testGetByStudentIdAndMultiClassId()
    {
        $this->mockDataObject(['multiClassId' => 1]);

        $res = $this->getAssistantStudentDao()->getByStudentIdAndMultiClassId(1, 1);

        $this->assertEquals(1, $res['assistantId']);
    }

    public function testFindByAssistantIdAndCourseId()
    {
        $this->mockDataObject(['courseId' => 1]);

        $res = $this->getAssistantStudentDao()->findByAssistantIdAndCourseId(1, 1);

        $this->assertEquals(1, $res[0]['assistantId']);
    }

    public function testFindByMultiClassIdAndStudentIds()
    {
        $this->mockDataObject(['courseId' => 1]);

        $res = $this->getAssistantStudentDao()->findByAssistantIdAndCourseId(1, 1);

        $this->assertEquals(1, $res[0]['assistantId']);
    }

    public function testFindByMultiClassId()
    {
        $this->mockDataObject(['multiClassId' => 1]);

        $res = $this->getAssistantStudentDao()->findByMultiClassId(1);

        $this->assertEquals(1, $res[0]['assistantId']);
    }

    public function testFindByMultiClassIdAndGroupId()
    {
        $this->mockDataObject(['multiClassId' => 1, 'group_id' => 1]);

        $res = $this->getAssistantStudentDao()->findByMultiClassIdAndGroupId(1, 1);

        $this->assertEquals(1, $res[0]['group_id']);
        $this->assertEquals(1, $res[0]['multiClassId']);
    }

    public function testCountMultiClassGroupStudentByGroupIds()
    {
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 1, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 2, 'courseId' => 2, 'multiClassId' => 2, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 2, 'studentId' => 3, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);

        $result = $this->getAssistantStudentDao()->countMultiClassGroupStudentByGroupIds(1, [1, 2]);

        $this->assertEquals(2, $result[0]['studentNum']);
    }

    public function testUpdateMultiClassStudentsGroup()
    {
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 1, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 2, 'courseId' => 2, 'multiClassId' => 2, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 2, 'studentId' => 3, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);

        $this->getAssistantStudentDao()->updateMultiClassStudentsGroup(1, ['groupId' => 2, 'studentIds' => [1]]);
        $result = $this->getAssistantStudentDao()->findByMultiClassIdAndGroupId(1, 2);

        $this->assertEquals(1, $result[0]['studentId']);
    }

    public function testFindByMultiClassIds()
    {
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 1, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 1, 'studentId' => 2, 'courseId' => 2, 'multiClassId' => 2, 'group_id' => 1]);
        $this->mockDataObject(['assistantId' => 2, 'studentId' => 3, 'courseId' => 1, 'multiClassId' => 1, 'group_id' => 1]);

        $result = $this->getAssistantStudentDao()->findByMultiClassIds([1, 3]);

        $this->assertEquals(2, count($result));
    }

    protected function getDefaultMockFields()
    {
        return [
            'assistantId' => 1,    // 助教ID
            'studentId' => 1,    // 学员ID
            'courseId' => 1, // 课程ID
            'multiClassId' => 1,  // 班课ID
            'group_id' => 1,  // 分组ID
        ];
    }

    /**
     * @return AssistantStudentDao
     */
    protected function getAssistantStudentDao()
    {
        return $this->createDao('Assistant:AssistantStudentDao');
    }
}
