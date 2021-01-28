<?php

namespace Tests\Unit\UserLearnStatistics\Service;

use Biz\BaseTestCase;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;

class LearnStatisticsServiceTest extends BaseTestCase
{
    public function testBatchDeletePastDailyStatistics()
    {
        $this->createDao('UserLearnStatistics:DailyStatisticsDao')->create(['userId' => 1]);
        $this->createDao('UserLearnStatistics:DailyStatisticsDao')->create(['userId' => 12, 'isStorage' => 1]);
        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics([
            'isStorage' => '1',
        ]);
        $result = $this->getLearnStatisticsService()->searchDailyStatistics([], [], 0, \PHP_INT_MAX);

        $this->assertEquals(1, $result[0]['id']);
    }

    public function testSearchLearnData()
    {
        $this->mockData();
        $result = $this->getLearnStatisticsService()->searchLearnData(['createdTime_GE' => 1, 'createdTime_LT' => 10], []);
        $this->assertArrayEquals([
            'learnedSeconds' => 3,
            'paidAmount' => 4,
            'refundAmount' => 4,
            'finishedTaskNum' => 5,
            'joinedClassroomNum' => 122,
            'exitClassroomNum' => 122,
            'joinedCourseSetNum' => 0,
            'exitCourseSetNum' => 0,
            'joinedCourseNum' => 122,
            'exitCourseNum' => 122,
            'userId' => 2,
        ], $result[0]);
    }

    private function mockData()
    {
        $this->mockBiz('Activity:ActivityLearnLogService', [
            [
                'functionName' => 'sumLearnTimeGroupByUserId',
                'returnValue' => [
                    2 => [
                        'userId' => 2,
                        'learnedTime' => 3,
                    ],
                ],
            ],
        ]);

        $this->mockBiz('Pay:AccountService', [
            [
                'functionName' => 'sumAmountGroupByUserId',
                'returnValue' => [
                    2 => [
                        'userId' => 2,
                        'amount' => 4,
                    ],
                ],
            ],
        ]);

        $this->mockBiz('Task:TaskResultService', [
            [
                'functionName' => 'countTaskNumGroupByUserId',
                'returnValue' => [
                    2 => [
                        'userId' => 2,
                        'count' => 5,
                    ],
                ],
            ],
        ]);

        $this->mockBiz('MemberOperation:MemberOperationService', [
            [
                'functionName' => 'countGroupByUserId',
                'returnValue' => [
                    2 => [
                        'userId' => 2,
                        'count' => 122,
                    ],
                ],
            ],
        ]);
    }

    public function testUpdateStorageByIds()
    {
        $result = $this->createDao('UserLearnStatistics:DailyStatisticsDao')->create(
           ['userId' => 1]
        );
        $this->assertEquals(0, $result['isStorage']);
        $this->getLearnStatisticsService()->updateStorageByIds([1]);
        $result = $this->createDao('UserLearnStatistics:DailyStatisticsDao')->get($result['id']);
        $this->assertEquals(1, $result['isStorage']);
    }

    public function testGetRecordEndTime()
    {
        $result = $this->getLearnStatisticsService()->getRecordEndTime();
        $settings = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertEquals(date('Y-m-d', time() - $settings['timespan']), $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testSearchLearnDataError()
    {
        $this->getLearnStatisticsService()->searchLearnData([], []);
    }

    public function testBatchCreateTotalStatistics()
    {
        $this->mockData();
        $result = $this->getLearnStatisticsService()->batchCreateTotalStatistics(['createdTime_GE' => 1, 'createdTime_LT' => 10]);
        $result = $this->getLearnStatisticsService()->searchTotalStatistics([], [], 0, \PHP_INT_MAX);
        $this->assertArrayEquals([
            'learnedSeconds' => 3,
            'paidAmount' => 4,
            'refundAmount' => 4,
            'finishedTaskNum' => 5,
            'joinedClassroomNum' => 122,
            'exitClassroomNum' => 122,
            'joinedCourseSetNum' => 0,
            'exitCourseSetNum' => 0,
            'joinedCourseNum' => 122,
            'exitCourseNum' => 122,
            'userId' => 2,
        ], $result[0]);
    }

    public function testBatchCreatePastDailyStatistics()
    {
        $this->mockData();
        $result = $this->getLearnStatisticsService()->batchCreatePastDailyStatistics(['createdTime_GE' => 1, 'createdTime_LT' => 10]);
        $result = $this->getLearnStatisticsService()->searchDailyStatistics([], [], 0, \PHP_INT_MAX);
        $this->assertArrayEquals([
            'learnedSeconds' => 3,
            'paidAmount' => 4,
            'refundAmount' => 4,
            'finishedTaskNum' => 5,
            'joinedClassroomNum' => 122,
            'exitClassroomNum' => 122,
            'joinedCourseSetNum' => 0,
            'exitCourseSetNum' => 0,
            'joinedCourseNum' => 122,
            'exitCourseNum' => 122,
            'userId' => 2,
        ], $result[0]);
    }

    public function testBatchCreateDailyStatistics()
    {
        $this->mockData();
        $result = $this->getLearnStatisticsService()->batchCreateDailyStatistics(['createdTime_GE' => 1, 'createdTime_LT' => 10]);
        $result = $this->getLearnStatisticsService()->searchDailyStatistics([], [], 0, \PHP_INT_MAX);
        $this->assertArrayEquals([
            'learnedSeconds' => 3,
            'paidAmount' => 4,
            'refundAmount' => 4,
            'finishedTaskNum' => 5,
            'joinedClassroomNum' => 122,
            'exitClassroomNum' => 122,
            'joinedCourseSetNum' => 0,
            'exitCourseSetNum' => 0,
            'joinedCourseNum' => 122,
            'exitCourseNum' => 122,
            'userId' => 2,
        ], $result[0]);
    }

    public function testStorageDailyStatistics()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => [
                'syncTotalDataStatus' => true,
            ]],
        ]);
        $daiyl = $this->createDao('UserLearnStatistics:DailyStatisticsDao')->create(
           [
               'userId' => 1,
               'joinedCourseNum' => 3,
               'exitCourseNum' => 45,
            ]
        );

        $total = $this->createDao('UserLearnStatistics:TotalStatisticsDao')->create(
           ['userId' => 1, 'joinedCourseNum' => 3]
        );
        $this->getLearnStatisticsService()->storageDailyStatistics();

        $result = $this->createDao('UserLearnStatistics:TotalStatisticsDao')->get($total['id']);
        $this->assertEquals(45, $result['exitCourseNum']);
        $this->assertEquals(6, $result['joinedCourseNum']);
    }

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

        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => [
                'currentTime' => 123,
                'timespan' => 312,
            ]],
        ]);

        $result = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->assertEquals(123, $result['currentTime']);
        $this->assertEquals(312, $result['timespan']);
    }

    public function testStatisticsDataSearch()
    {
        $conditions = [
            'startDate' => '2017-11-08',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => [3],
        ];
        $order = [
            'id' => 'DESC',
        ];
        $preResult = [
            [
                'userId' => 3,
                'joinedClassroomNum' => 2,
                'joinedCourseSetNum' => 2,
            ],
        ];

        $this->mockBiz('UserLearnStatistics:DailyStatisticsDao');
        $this->getDailyStatisticsDao()->shouldReceive('statisticSearch')->andReturn($preResult);
        $result = $this->getLearnStatisticsService()->statisticsDataSearch($conditions, $order);

        $this->assertEquals($result[0]['userId'], $preResult[0]['userId']);
        $this->assertEquals($result[0]['joinedClassroomNum'], $preResult[0]['joinedClassroomNum']);
    }

    public function testStatisticsDataCount()
    {
        $conditions = [
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
            'userIds' => [3],
        ];

        $this->mockBiz('UserLearnStatistics:TotalStatisticsDao');
        $this->getTotalStatisticsDao()->shouldReceive('statisticCount')->andReturn(1);

        $result = $this->getLearnStatisticsService()->statisticsDataCount($conditions);
        $this->assertEquals($result, 1);
    }

    public function testGetUserOverview()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'findUserLearnCourseIds', 'returnValue' => [1, 2]],
            ['functionName' => 'countUserLearnCourses', 'returnValue' => 10],
        ]);

        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'countUserLearnCourseSets', 'returnValue' => 5],
        ]);

        $this->mockBiz('Course:LearningDataAnalysisService', [
            ['functionName' => 'getUserLearningProgressByCourseIds', 'returnValue' => ['percent' => 90]],
        ]);

        $this->mockBiz('Course:CourseNoteService', [
            ['functionName' => 'countCourseNotes', 'returnValue' => 6],
        ]);

        $this->mockBiz('Course:ThreadService', [
            ['functionName' => 'countPartakeThreadsByUserId', 'returnValue' => 2],
        ]);

        $this->mockBiz('Thread:ThreadService', [
            ['functionName' => 'countPartakeThreadsByUserIdAndTargetType', 'returnValue' => 2],
        ]);

        $this->mockBiz('Review:ReviewService', [
            ['functionName' => 'countReviews', 'returnValue' => 10],
        ]);

        $result = $this->getLearnStatisticsService()->getUserOverview($user->getId());
        $this->assertEquals('10', $result['learningCoursesCount']);
        $this->assertEquals('5', $result['learningCourseSetCount']);
        $this->assertEquals('90', $result['learningProcess']['percent']);
        $this->assertEquals('6', $result['learningCourseNotesCount']);
        $this->assertEquals('4', $result['learningCourseThreadsCount']);
        $this->assertEquals('10', $result['learningReviewCount']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testGetUserOverviewWithUserNotFound()
    {
        $user = $this->getCurrentUser();
        $this->getLearnStatisticsService()->getUserOverview($user->getId() + 1);
    }

    public function testGetUserOverviewWithEmpty()
    {
        $user = $this->getCurrentUser();

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'findUserLearnCourseIds', 'returnValue' => []],
            ['functionName' => 'countUserLearnCourses', 'returnValue' => 10],
        ]);

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

        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'searchMembers', 'returnValue' => [
                1 => ['courseId' => 2, 'orderId' => 2, 'classroomId' => 1],
            ]],
        ]);

        $this->mockBiz('Order:OrderService', [
            ['functionName' => 'findOrdersByIds', 'returnValue' => []],
        ]);

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'searchCourses', 'returnValue' => [
                1 => ['id' => 1, 'courseSetId' => 1],
            ]],
        ]);

        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'findClassroomsByIds', 'returnValue' => [
                1 => ['id' => 1],
            ]],
        ]);

        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'findCourseSetsByIds', 'returnValue' => [
                1 => ['id' => 1],
            ]],
        ]);

        $this->mockBiz('Course:LearningDataAnalysisService', [
            ['functionName' => 'getUserLearningProgress', 'returnValue' => ['percent' => 90]],
        ]);

        list($learnCourses, $courseSets, $members) = $this->getLearnStatisticsService()->findLearningCourseDetails($user->getId(), 1, 10);
        $this->assertEquals(1, count($learnCourses));
        $this->assertEquals(1, count($courseSets));
        $this->assertEquals(1, count($members));
    }

    public function testSearchTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(['userId' => 1]);
        $this->getTotalStatisticsDao()->create(['userId' => 2]);

        $result = $this->getLearnStatisticsService()->searchTotalStatistics([], [], 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics(['userId' => 1], [], 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchTotalStatistics([], [], 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountTotalStatistics()
    {
        $this->getTotalStatisticsDao()->create(['userId' => 1]);
        $this->getTotalStatisticsDao()->create(['userId' => 2]);

        $count = $this->getLearnStatisticsService()->countTotalStatistics([]);
        $this->assertEquals(2, $count);

        $count = $this->getLearnStatisticsService()->countTotalStatistics(['userId' => 1]);
        $this->assertEquals(1, $count);
    }

    public function testSearchDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(['userId' => 1]);
        $this->getDailyStatisticsDao()->create(['userId' => 2]);
        $result = $this->getLearnStatisticsService()->searchDailyStatistics([], [], 0, 2);
        $this->assertEquals(2, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics(['userId' => 1], [], 0, 2);
        $this->assertEquals(1, count($result));

        $result = $this->getLearnStatisticsService()->searchDailyStatistics([], [], 0, 1);
        $this->assertEquals(1, count($result));
    }

    public function testCountDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(['userId' => 1]);
        $this->getDailyStatisticsDao()->create(['userId' => 2]);
        $count = $this->getLearnStatisticsService()->countDailyStatistics([]);

        $this->assertEquals(2, $count);
    }

    public function batchDeletePastDailyStatistics()
    {
        $this->getDailyStatisticsDao()->create(['userId' => 1, 'recordTime' => 2]);
        $this->getDailyStatisticsDao()->create(['userId' => 1, 'recordTime' => 3]);
        $this->getDailyStatisticsDao()->create(['userId' => 1, 'recordTime' => 10]);
        $this->getDailyStatisticsDao()->create(['userId' => 2, 'recordTime' => 3]);

        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(['recordTime_GE' => 10]);
        $this->assertEquals(3, $this->getDailyStatisticsDao()->count([]));
        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(['userIds' => ['1']]);
        $this->assertEquals(1, $this->getDailyStatisticsDao()->count([]));
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
