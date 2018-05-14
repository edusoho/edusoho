<?php

namespace Tests\Unit\Course\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\ReportService;

class ReportServiceTest extends BaseTestCase
{
    public function testSummary()
    {
        $mockStartTime = strtotime('2017-07-01');
        $course = $this->mockCourse(array('id' => 1, 'compulsoryTaskNum' => '3'));
        $this->mockCourseMember(array('courseId' => 1, 'learnedCompulsoryTaskNum' => 3, 'lastLearnTime' => $mockStartTime + 24 * 60 * 60));
        $this->mockTaskTryView(array('courseId' => 1));
        $this->mockCourseNote();
        $this->mockThread();
        $this->mockThread(array('type' => 'discussion'));
        $this->getReportService()->mockStartTime($mockStartTime);
        $result = $this->getReportService()->summary(1);
        $expect = array(
            'studentNum' => 1,
            'studentNumToday' => 1,
            'finishedNum' => 1,
            'finishedNumToday' => 1,
            'tryViewNum' => 1,
            'tryViewNumToday' => 1,
            'noteNum' => 1,
            'noteNumToday' => 1,
            'askNum' => 1,
            'askNumToday' => 1,
            'discussionNum' => 1,
            'discussionNumToday' => 1,
        );
        $this->assertArrayEquals($expect, $result);
    }

    public function testGetCompletionRateTrend()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'studentNum' => 10)),
        ));

        $result = $this->getReportService()->getCompletionRateTrend(1, '2017-07-01', '2017-07-10');

        $this->assertCount(10, $result);
    }

    public function testGetStudentTrend()
    {
        $result = $this->getReportService()->getStudentTrend(1, array('startDate' => '2017-07-01', 'endDate' => '2017-07-10'));
        $this->assertCount(10, $result);
    }

    public function testGetStudentDetail()
    {
        $this->mockBiz(
            'User:UserService', array(
                array(
                    'functionName' => 'searchUsers',
                    'withParams' => array(
                        array('userIds' => array(1, 2)),
                        array(),
                        0,
                        2,
                    ),
                    'returnValue' => array(
                        array('id' => 1, 'nickname' => 'user1'),
                        array('id' => 2, 'nickname' => 'user2'),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskService', array(
                array(
                    'functionName' => 'searchTasks',
                    'withParams' => array(
                        array(
                            'courseId' => 1,
                            'isOptional' => 0,
                            'status' => 'published',
                        ),
                        array('seq' => 'ASC'),
                        0,
                        20,
                    ),
                    'returnValue' => array(
                        array('id' => 1, 'courseId' => '1'),
                        array('id' => 2, 'courseId' => '1'),
                    ),
                ),
            )
        );

        $this->mockBiz(
            'Task:TaskResultService', array(
                array(
                    'functionName' => 'searchTaskResults',
                    'withParams' => array(
                        array(
                            'courseId' => 1,
                            'userIds' => array(1, 2),
                            'courseTaskIds' => array(1, 2),
                        ),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                    'returnValue' => array(
                        array('userId' => 1, 'courseTaskId' => '1'),
                        array('userId' => 1, 'courseTaskId' => '2'),
                        array('userId' => 2, 'courseTaskId' => '1'),
                        array('userId' => 2, 'courseTaskId' => '2'),
                    ),
                ),
            )
        );
        $result = $this->getReportService()->getStudentDetail(1, array(1, 2));
        $this->assertEquals(3, count($result));
    }

    public function testBuildStudentDetailOrderBy()
    {
        $conditions1 = array('orderBy' => 'createdTimeDesc');
        $conditions2 = array('orderBy' => 'createdTimeAsc');
        $conditions3 = array('orderBy' => 'learnedCompulsoryTaskNumDesc');
        $conditions4 = array('orderBy' => 'learnedCompulsoryTaskNumAsc');
        $result1 = $this->getReportService()->buildStudentDetailOrderBy($conditions1);
        $result2 = $this->getReportService()->buildStudentDetailOrderBy($conditions2);
        $result3 = $this->getReportService()->buildStudentDetailOrderBy($conditions3);
        $result4 = $this->getReportService()->buildStudentDetailOrderBy($conditions4);
        $this->assertArrayEquals(array('createdTime' => 'DESC'), $result1);
        $this->assertArrayEquals(array('createdTime' => 'ASC'), $result2);
        $this->assertArrayEquals(array('learnedCompulsoryTaskNum' => 'DESC'), $result3);
        $this->assertArrayEquals(array('learnedCompulsoryTaskNum' => 'ASC'), $result4);
    }

    public function testBuildStudentDetailConditions()
    {
        $course = $this->mockCourse(array('id' => 1, 'compulsoryTaskNum' => '3'));
        $this->mockBiz(
            'User:UserService', array(
                array(
                    'functionName' => 'getUserByVerifiedMobile',
                    'withParams' => array(
                        '18435180001',
                    ),
                    'returnValue' => array('id' => 1, 'nickname' => 'user name'),
                ),
            )
        );
        $conditions = array(
            'range' => 'unLearnedSevenDays',
            'nameOrMobile' => '18435180001',
        );
        $result = $this->getReportService()->buildStudentDetailConditions($conditions, 1);
        $expect = array('courseId' => 1, 'role' => 'student', 'learnedCompulsoryTaskNumLT' => 3, 'userIds' => array(1));
        $this->assertArrayEquals($expect, array('courseId' => $result['courseId'], 'role' => $result['role'], 'learnedCompulsoryTaskNumLT' => $result['learnedCompulsoryTaskNumLT'], 'userIds' => $result['userIds']));
    }

    public function testSearchUserIdsByCourseIdAndFilterAndSortAndKeyword()
    {
        $this->mockCourseMember(array('userId' => 1, 'courseId' => 1, 'isLearned' => '1', 'learnedCompulsoryTaskNum' => 1));
        $this->mockCourseMember(array('userId' => 2, 'courseId' => 1, 'isLearned' => '0', 'learnedCompulsoryTaskNum' => 2));
        $this->mockCourseMember(array('userId' => 3, 'courseId' => 1, 'isLearned' => '0', 'learnedCompulsoryTaskNum' => 3));

        $result1 = $this->getReportService()->searchUserIdsByCourseIdAndFilterAndSortAndKeyword(1, 'all', 'createdTimeDesc', 0, PHP_INT_MAX);
        $result2 = $this->getReportService()->searchUserIdsByCourseIdAndFilterAndSortAndKeyword(1, 'unFinished', 'createdTimeAsc', 0, PHP_INT_MAX);
        $result3 = $this->getReportService()->searchUserIdsByCourseIdAndFilterAndSortAndKeyword(1, 'unFinished', 'CompletionRateDAsc', 0, PHP_INT_MAX);
        $result4 = $this->getReportService()->searchUserIdsByCourseIdAndFilterAndSortAndKeyword(1, 'unFinished', 'CompletionRateDesc', 0, PHP_INT_MAX);

        $this->assertEquals(array(1, 2, 3), $result1);
        $this->assertEquals(array(2, 3), $result2);
        $this->assertEquals(array(2, 3), $result3);
        $this->assertEquals(array(3, 2), $result4);
    }

    public function testGetCourseTaskLearnData()
    {
        $this->mockCourse(array('id' => 1, 'studentNum' => 3));
        $this->mockTaskResult(array('courseTaskId' => 1, 'userId' => 1, 'status' => 'finish'));
        $this->mockTaskResult(array('courseTaskId' => 1, 'userId' => 2, 'status' => 'finish'));
        $this->mockTaskResult(array('courseTaskId' => 1, 'userId' => 3, 'status' => 'start'));
        $tasks = array(
            array('id' => 1, 'status' => 'published'),
        );
        $result = $this->getReportService()->getCourseTaskLearnData($tasks, 1);
        $expect = array(array(
            'id' => 1,
            'status' => 'published',
            'finishedNum' => 2,
            'learnNum' => 1,
            'notStartedNum' => 0,
            'rate' => (float) 66.667,
        ));
        $this->assertArrayEquals($expect, $result);
    }

    /**
     * @return ReportService
     */
    private function getReportService()
    {
        return $this->createService('Course:ReportService');
    }

    protected function mockCourseMember($fileds = array())
    {
        $defaultFileds = array(
            'courseId' => 1,
            'classroomId' => 0,
            'joinedType' => 'course',
            'userId' => 1,
            'role' => 'student',
            'learnedCompulsoryTaskNum' => 1,
            'courseSetId' => 1,
        );
        $courseMember = array_merge($defaultFileds, $fileds);

        return $this->getCourseMemberDao()->create($courseMember);
    }

    protected function mockCourse($fileds = array())
    {
        $defaultFileds = array(
            'courseSetId' => 1,
            'title' => 'course title',
            'summary' => '<p>summary</p>',
            'status' => 'published',
        );
        $course = array_merge($defaultFileds, $fileds);

        return $this->getCourseDao()->create($course);
    }

    protected function mockTaskTryView($fileds = array())
    {
        $defaultFileds = array(
            'userId' => 1,
            'courseSetId' => 1,
            'courseId' => 1,
            'taskId' => 1,
            'taskType' => 'text',
        );
        $course = array_merge($defaultFileds, $fileds);

        return $this->getTryViewLogDao()->create($course);
    }

    protected function mockThread($fileds = array())
    {
        $defaultFileds = array(
            'userId' => 1,
            'courseId' => 1,
            'taskId' => 1,
            'type' => 'question',
            'courseSetId' => 1,
            'title' => 'title',
        );
        $thread = array_merge($defaultFileds, $fileds);

        return $this->getThreadDao()->create($thread);
    }

    protected function mockCourseNote($fileds = array())
    {
        $defaultFileds = array(
            'userId' => 1,
            'courseId' => 1,
            'taskId' => 1,
            'content' => 'content',
            'courseSetId' => 1,
        );
        $thread = array_merge($defaultFileds, $fileds);

        return $this->getCourseNoteDao()->create($thread);
    }

    protected function mockTaskResult($fileds = array())
    {
        $defaultFileds = array(
            'userId' => 1,
            'courseId' => 1,
            'courseTaskId' => 1,
            'status' => 'finish',
        );
        $taskResult = array_merge($defaultFileds, $fileds);

        return $this->getTaskResultDao()->create($taskResult);
    }

    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    private function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    private function getTryViewLogDao()
    {
        return $this->createDao('Task:TryViewLogDao');
    }

    private function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    private function getCourseNoteDao()
    {
        return $this->createDao('Course:CourseNoteDao');
    }

    private function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}
