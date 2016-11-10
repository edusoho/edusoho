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
            'studentNum' => 100,
            'noteNum' => 10,
            'finishedNum' => 100,//完成人数
            'askNum' => 20,
            'discussionNum' => 20,
            'finishedRate' => 100,
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'searchMemberCount', 'runTimes' => 2, 'returnValue' => 100)
        ));

        $this->mock('Course.ThreadService', array(
            array('functionName' => 'searchThreadCount', 'runTimes' => 2, 'returnValue' => 20)
        ));

        $this->mock('Course.NoteService', array(
            array('functionName' => 'searchNoteCount', 'runTimes' => 1, 'returnValue' => 10)
        ));

        $summary = $this->getReportService()->summary(1);

        $this->assertEquals($fakeCourse, $summary);
    }

    public function testGetLateMonthLearndData()
    {
        $fakeMembers = array(
            array('createdTime' => strtotime(date('- 13 days')), 'finishedTime' => strtotime(date('- 13 days')), 'isLearned' => 1),
            array('createdTime' => strtotime(date('- 10 days')), 'finishedTime' => strtotime(date('- 10 days')), 'isLearned' => 1),
            array('createdTime' => strtotime(date('- 6 days')), 'finishedTime' => strtotime(date('- 6 days')), 'isLearned' => 1),
            array('createdTime' => strtotime(date('- 3 days')), 'finishedTime' => strtotime(date('- 3 days')), 'isLearned' => 1),
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'searchMemberCount', 'runTimes' => 2, 'returnValue' => 100),
            array('functionName' => 'searchMembers', 'runTimes' => 1, 'returnValue' => $fakeMembers)
        ));

        $fakeThreads = array(
            array('createdTime' => strtotime(date('- 13 days'))),
            array('createdTime' => strtotime(date('- 3 days'))),
            array('createdTime' => strtotime(date('- 1 days'))),
        );
        $this->mock('Course.ThreadService', array(
            array('functionName' => 'searchThreadCount', 'runTimes' => 2, 'returnValue' => 20),
            array('functionName' => 'searchThreads', 'runTimes' => 2, 'returnValue' => $fakeThreads)
        ));

        $fakeNotes = array(
            array('createdTime' => strtotime(date('- 13 days'))),
            array('createdTime' => strtotime(date('- 3 days'))),
            array('createdTime' => strtotime(date('- 1 days'))),
        );
        $this->mock('Course.NoteService', array(
            array('functionName' => 'searchNoteCount', 'runTimes' => 1, 'returnValue' => 20),
            array('functionName' => 'searchNotes', 'runTimes' => 1, 'returnValue' => $fakeNotes)
        ));

        $lateMonthLearndData = $this->getReportService()->getLateMonthLearndData(1);

        $this->assertCount(30, $lateMonthLearndData);
    }

    public function testGetCourseLessonLearnStat()
    {
        $fakeLessons = array(
            array('number' => 1, 'id' => 1),
            array('number' => 2, 'id' => 2)
        );
        $this->mock('Course.CourseService', array(
            array('functionName' => 'getCourseLessons', 'runTimes' => 1, 'returnValue' => $fakeLessons),
            array('functionName' => 'searchLearnCount', 'runTimes' => 2, 'returnValue' => 10),
            array('functionName' => 'findCourseTeachers', 'runTimes' => 1, 'returnValue' => array(array(
                'userId' => 1
            ))),
        ));

        $stat = $this->getReportService()->getCourseLessonLearnStat(1);

        $this->assertCount(2, $stat);
        $this->assertEquals(10, array_pop($stat)['finishedNum']);
        $this->assertEquals(10, array_pop($stat)['learnNum']);
    }

    protected function getReportService()
    {
        return $this->getServiceKernel()->createService('Course.ReportService');
    }
}
