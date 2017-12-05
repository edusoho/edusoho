<?php

namespace Tests\Unit\UserLearnStatistics;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;

class LearnStatisticsServiceTest extends BaseTestCase
{
    public function testStatisticsDataSearch()
    {
        $conditions = array(
            'startDate' => '2017-11-08',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => array(3),
        );
        $order = array(
            'id' => 'DESC',
        );
        $preResult = array(
            array(
                'userId' => 3,
                'joinedClassroomNum' => 2,
                'joinedCourseSetNum' => 2,
            ),
        );

        $this->mockBiz('UserLearnStatistics:DailyStatisticsDao');
        $this->getDailyStatisticsDao()->shouldReceive('statisticSearch')->andReturn($preResult);
        $result = $this->getLearnStatisticsService()->statisticsDataSearch($conditions, $order);

        $this->assertEquals($result[0]['userId'], $preResult[0]['userId']);
        $this->assertEquals($result[0]['joinedClassroomNum'], $preResult[0]['joinedClassroomNum']);
    }

    public function testStatisticsDataCount()
    {
        $conditions = array(
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => array(3),
        );

        $this->mockBiz('UserLearnStatistics:TotalStatisticsDao');
        $this->getTotalStatisticsDao()->shouldReceive('statisticCount')->andReturn(1);

        $result = $this->getLearnStatisticsService()->statisticsDataCount($conditions);
        $this->assertEquals($result, 1);
    }

    public function testGetUserOverview()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findUserLearnCourseIds', 'returnValue' => array(1, 2)),
            array('functionName' => 'countUserLearnCourses', 'returnValue' => 10),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'countUserLearnCourseSets', 'returnValue' => 5),
        ));

        $this->mockBiz('Course:LearningDataAnalysisService', array(
            array('functionName' => 'getUserLearningProgressByCourseIds', 'returnValue' => array('percent' => 90)),
        ));

        $this->mockBiz('Course:CourseNoteService', array(
            array('functionName' => 'countCourseNotes', 'returnValue' => 6),
        ));

        $this->mockBiz('Course:ThreadService', array(
            array('functionName' => 'countPartakeThreadsByUserId', 'returnValue' => 2),
        ));

        $this->mockBiz('Course:ReviewService', array(
            array('functionName' => 'searchReviewsCount', 'returnValue' => 7),
        ));

        $this->mockBiz('Classroom:ClassroomReviewService', array(
            array('functionName' => 'searchReviewCount', 'returnValue' => 3),
        ));

        $result = $this->getLearnStatisticsService()->getUserOverview($user->getId());
        $this->assertEquals('10', $result['learningCoursesCount']);
        $this->assertEquals('5', $result['learningCourseSetCount']);
        $this->assertEquals('90', $result['learningProcess']['percent']);
        $this->assertEquals('6', $result['learningCourseNotesCount']);
        $this->assertEquals('2', $result['learningCourseThreadsCount']);
        $this->assertEquals('10', $result['learningReviewCount']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @expectedExceptionMessage 用户不存在！
     */
    public function testGetUserOverviewWithUserNotFound()
    {
        $user = $this->getCurrentUser();
        $this->getLearnStatisticsService()->getUserOverview($user->getId() + 1);
    }

    public function testGetUserOverviewWithEmpty()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findUserLearnCourseIds', 'returnValue' => array()),
            array('functionName' => 'countUserLearnCourses', 'returnValue' => 10),
        ));

        $result = $this->getLearnStatisticsService()->getUserOverview($user->getId());
        $this->assertEmpty($result);
    }

    public function testGetLearningCourseDetailsWithNoData()
    {
        $user = $this->getCurrentUser();
        $result = $this->getLearnStatisticsService()->getLearningCourseDetails($user->getId(), 1, 10);
        $this->assertEquals(3, count($result));
    }

    public function testGetLearningCourseDetails()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'searchMembers', 'returnValue' => array(
                1 => array('courseId' => 2, 'orderId' => 2),
            )),
        ));

        $this->mockBiz('Order:OrderService', array(
            array('functionName' => 'findOrdersByIds', 'returnValue' => array()),
        ));

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'searchCourses', 'returnValue' => array(
                1 => array('id' => 1, 'courseSetId' => 1),
            )),
        ));

        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'findCourseSetsByIds', 'returnValue' => array(
                1 => array('id' => 1),
            )),
        ));

        $this->mockBiz('Course:LearningDataAnalysisService', array(
            array('functionName' => 'getUserLearningProgress', 'returnValue' => array('percent' => 90)),
        ));

        list($learnCourses, $courseSets, $members) = $this->getLearnStatisticsService()->getLearningCourseDetails($user->getId(), 1, 10);
        $this->assertEquals(1, count($learnCourses));
        $this->assertEquals(1, count($courseSets));
        $this->assertEquals(1, count($members));
    }

    /**
     * @return LearnStatisticsService
     */
    protected function getLearnStatisticsService()
    {
        return $this->createService('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getTotalStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:TotalStatisticsDao');
    }

    protected function getDailyStatisticsDao()
    {
        return $this->createDao('UserLearnStatistics:DailyStatisticsDao');
    }
}
