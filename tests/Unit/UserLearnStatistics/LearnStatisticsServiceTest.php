<?php

namespace Tests\Unit\UserLearnStatistics;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;

class LearnStatisticsServiceTest extends BaseTestCase
{
    public function testUpdateStorageByIds()
    {
        $result =  $this->createDao('UserLearnStatistics:DailyStatisticsDao')->create(
           array('userId' => 1 )
        );
        $this->assertEquals(0,  $result['isStorage']);
        $this->getLearnStatisticsService()->updateStorageByIds(array(1));
        $result = $this->createDao('UserLearnStatistics:DailyStatisticsDao')->get($result['id']);
        $this->assertEquals(1,  $result['isStorage']);
    }

    public function testGetRecordEndTime()
    {
        $result = $this->getLearnStatisticsService()->getRecordEndTime();
        $settings = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertEquals(date('Y-m-d', time() - $settings['timespan']), $result);
    }

    // public function testBatchCreateTotalStatistics()
    // {
    // }

    // public function testBatchCreatePastDailyStatistics()
    // {
    // }

    // public function testBatchCreateDailyStatistics()
    // {

    // }

    // public function testBatchDeletePastDailyStatistics()
    // {
    // }

    // public function testSearchLearnData()
    // {
    // }

    // public function testStorageDailyStatistics()
    // {
    // }
    
    public function testSetStatisticsSetting()
    {
         $result = $this->getLearnStatisticsService()->setStatisticsSetting();
         $this->assertEquals(strtotime(date('Y-m-d')), $result['currentTime']);
         $this->assertEquals(24 * 60 * 60 * 365, $result['timespan']);
    }

    public function testGetStatisticsSetting()
    {
        $result = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertEquals(strtotime(date('Y-m-d')), $result['currentTime']);
        $this->assertEquals(24 * 60 * 60 * 365, $result['timespan']);

         $this->mockBiz('System:SettingService', array(
            array('functionName' => 'get', 'returnValue' => array(
                'currentTime' => 123,
                'timespan' => 312,
            )),
        ));

        $result = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertEquals(123, $result['currentTime']);
        $this->assertEquals(312, $result['timespan']);
    }

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

        $this->mockBiz('Thread:ThreadService', array(
            array('functionName' => 'countPartakeThreadsByUserIdAndTargetType', 'returnValue' => 2),
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
        $this->assertEquals('4', $result['learningCourseThreadsCount']);
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

    public function testFindLearningCourseDetailsWithNoData()
    {
        $user = $this->getCurrentUser();
        $result = $this->getLearnStatisticsService()->findLearningCourseDetails($user->getId(), 1, 10);
        $this->assertEquals(3, count($result));
    }

    public function testFindLearningCourseDetails()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'searchMembers', 'returnValue' => array(
                1 => array('courseId' => 2, 'orderId' => 2, 'classroomId' => 1),
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

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'findClassroomsByIds', 'returnValue' => array(
                1 => array('id' => 1),
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

        list($learnCourses, $courseSets, $members) = $this->getLearnStatisticsService()->findLearningCourseDetails($user->getId(), 1, 10);
        $this->assertEquals(1, count($learnCourses));
        $this->assertEquals(1, count($courseSets));
        $this->assertEquals(1, count($members));
    }

    public function testSearchTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(array('userId' => 1));
        $this->getTotalStatisticsDao()->create(array('userId' => 2));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array(), array(), 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array('userId' => 1), array(), 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(array(), array(), 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(array('userId' => 1));
        $this->getTotalStatisticsDao()->create(array('userId' => 2));

        $count = $this->getLearnStatisticsService()->countTotalStatistics(array());
        $this->assertEquals(2, $count);

        $count = $this->getLearnStatisticsService()->countTotalStatistics(array('userId' => 1));
        $this->assertEquals(1, $count);
    }

    public function testSearchDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1));
        $this->getDailyStatisticsDao()->create(array('userId' => 2));
        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array(), array(), 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array('userId' => 1), array(), 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics(array(), array(), 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1));
        $this->getDailyStatisticsDao()->create(array('userId' => 2));
        $count = $this->getLearnStatisticsService()->countDailyStatistics(array());

        $this->assertEquals(2, $count);
    }

    public function batchDeletePastDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 2));
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 3));
        $this->getDailyStatisticsDao()->create(array('userId' => 1, 'recordTime' => 10));
        $this->getDailyStatisticsDao()->create(array('userId' => 2, 'recordTime' => 3));

        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(array('recordTime_GE' => 10));
        $this->assertEquals(3, $this->getDailyStatisticsDao()->count(array()));
        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(array('userIds' => array('1')));
        $this->assertEquals(1, $this->getDailyStatisticsDao()->count(array()));
    }

    public function getStatisticsSetting()
    {
        $setting = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertNotEmpty($setting);
        $this->assertNotEmpty($setting['timespan']);
        $this->assertNotEmpty($setting['currentTime']);
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
