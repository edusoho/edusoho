<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\ReviewService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class ReportServiceTest extends BaseTestCase
{
    public function testSummary()
    {
        $fakeCourse = array(
            'studentNum' => 1,
            'noteNum' => 2,
            'finishedNum' => 100,//完成人数
            'askNum' => 20,
            'discussionNum' => 20
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'getCourse', 'runTimes' => 1, 'returnValue' => $fakeCourse),
            array('functionName' => 'searchMemberCount', 'runTimes' => 1, 'returnValue' => 100)
        ));

        $this->mock('Course.ThreadService', array(
            array('functionName' => 'searchThreadCount', 'runTimes' => 2, 'returnValue' => 20)
        ));

        $summary = $this->getReportService()->summary(1);

        $this->assertEquals($fakeCourse, $summary);
    }

    public function testGetLateMonthLearndData()
    {
        $students = array(
            array('createdTime' => 100, 'isLearned' => 1, 'finishedTime' => 100),
            array('createdTime' => 1477311115, 'isLearned' => 1, 'finishedTime' => 1477311115),
            array('createdTime' => 1477311115, 'isLearned' => 1, 'finishedTime' => 1477311115),
            array('createdTime' => 1477311115, 'isLearned' => 1, 'finishedTime' => 1477311115),
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'findCourseStudents', 'runTimes' => 1, 'returnValue' => $students)
        ));

        $lateMonthLearndData = $this->getReportService()->getLateMonthLearndData(1);

        $this->assertCount(30, $lateMonthLearndData);
        $this->assertEquals(1, array_pop($lateMonthLearndData)['studentNum']);
        $this->assertEquals(1, array_pop($lateMonthLearndData)['finishedNum']);
    }

    public function testGetCourseLessonLearnStat()
    {
        $fakeLessons = array(
            array('seq' => 1),
            array('seq' => 2)
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'getCourseLessons', 'runTimes' => 1, 'returnValue' => $fakeLessons),
            array('functionName' => 'searchLearnCount', 'runTimes' => 2, 'returnValue' => 10),
            array('functionName' => 'findLearnsCountByLessonId', 'runTimes' => 2, 'returnValue' => 20)
        ));

        $stat = $this->getReportService()->getCourseLessonLearnStat(1);

        $this->assertCount(2, $stat);
        $this->assertEquals(10, array_pop($stat)['finishedNum']);
        $this->assertEquals(20, array_pop($stat)['learnNum']);
    }

    protected function getReportService()
    {
        return $this->getServiceKernel()->createService('Course.ReportService');
    }
}
