<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class LearningDataAnalysisDaoTest extends BaseDaoTestCase
{
    public function testSumStatisticDataByCourseIdsAndUserId()
    {
        $result = $this->getDao()->sumStatisticDataByCourseIdsAndUserId([], 1);
        $this->assertEquals(['taskNum' => 0, 'learnedNum' => 0], $result);

        $course = $this->mockCourse();
        $courseMember = $this->mockCourseMember();
        $result = $this->getDao()->sumStatisticDataByCourseIdsAndUserId([1, 2], 1);
        $this->assertEquals(1, $result['taskNum']);
    }

    protected function getDefaultMockFields()
    {
        return [
        ];
    }

    private function mockCourseMember($fields = [])
    {
        $defaultFields = [
            'courseId' => '1',
            'classroomId' => '1',
            'joinedType' => 'course',
            'userId' => '1',
            'orderId' => '1',
            'deadline' => '1',
            'levelId' => '1',
            'learnedNum' => '1',
            'credit' => '1',
            'noteNum' => '1',
            'noteLastUpdateTime' => '1',
            'isLearned' => '1',
            'finishedTime' => '1',
            'seq' => '1',
            'remark' => 'asdf',
            'isVisible' => '1',
            'role' => 'student',
            'locked' => '1',
            'deadlineNotified' => '1',
            'lastLearnTime' => '1',
            'courseSetId' => '1',
            'lastViewTime' => '0',
            'refundDeadline' => '0',
            'learnedCompulsoryTaskNum' => '0',
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseMemberDao()->create($fields);
    }

    private function mockCourse($fields = [])
    {
        $defaultFields = [
            'courseSetId' => 1,
            'title' => 'a',
            'address' => 'a',
            'compulsoryTaskNum' => 1,
            'taskNum' => 1,
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseDao()->create($fields);
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
