<?php

namespace Tests\Unit\AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\UserLearnStatistics\UserCourseStatisticsExporter;
use Biz\BaseTestCase;

class UserCourseStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $result = [
            'user.learn.statistics.nickname',
            'user.learn.statistics.mobile',
            'classroom.name',
            'course.name',
            'user.learn.statistics.course',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.task_num',
            'user.learn.statistics.finish_task_num',
            'user.learn.statistics.finish_rate',
        ];

        $this->assertArrayEquals($exporter->getTitles(), $result);
    }

    public function testBuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'searchUsers',
                    'returnValue' => [['id' => 1]],
                ],
            ]
        );
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $conditions = [];
        $result = $exporter->buildCondition($conditions);
        $this->assertEquals([], $result['userIds']);

        $conditions = ['keyword' => 'la'];

        $result = $exporter->buildCondition($conditions);
        $this->assertEquals(1, $result['userIds'][0]);
        $this->assertEquals(0, $result['destroyed']);
        $this->assertNotTrue(isset($result['nickname']));
    }

    public function testCanExport()
    {
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $this->assertEquals(true, $exporter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $this->assertEquals(false, $exporter->canExport());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'countUsers',
                    'returnValue' => 3,
                ],
            ]
        );
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $this->assertEquals(3, $exporter->getCount());
    }

    public function testFindCourseMemberData()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseSetService',
            [
                [
                    'functionName' => 'findCourseSetsByIds',
                    'returnValue' => [
                        '1' => ['id' => 1, 'title' => 'testTitle'],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'findCoursesByCourseSetIds',
                    'returnValue' => [
                        ['id' => 1, 'title' => 'courseTitle', 'compulsoryTaskNum' => 5],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            [
                [
                    'functionName' => 'findClassroomsByIds',
                    'returnValue' => [
                        '1' => ['id' => 1, 'title' => 'classroomTitle'],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Visualization:ActivityLearnDataService',
            [
                [
                    'functionName' => 'searchCoursePlanLearnDailyData',
                    'returnValue' => [
                        ['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300],
                        ['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300],
                        ['userId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300],
                    ],
                ],
            ]
        );

        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $courseMembers = [
            ['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'learnedCompulsoryTaskNum' => 3, 'classroomId' => 1],
            ['userId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'learnedCompulsoryTaskNum' => 0, 'classroomId' => 0],
        ];
        $data = ReflectionUtils::invokeMethod($exporter, 'findCourseMemberData', [$courseMembers]);

        $this->assertEquals(60, $data[1][0]['finishRate']);
        $this->assertEquals('classroomTitle', $data[1][0]['classroomName']);
        $this->assertEquals(0, $data[2][0]['finishRate']);
        $this->assertEquals('', $data[2][0]['classroomName']);
    }

    public function testHandleStatistics()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $users = [['id' => 1, 'nickname' => 'test', 'verifiedMobile' => '11123455678']];
        $courseMemberData = [
            '1' => [[
                'classroomName' => 'test',
                'courseSetName' => 'test',
                'courseName' => 'test',
                'sumTime' => 120,
                'taskNum' => 5,
                'finishTaskNum' => 2,
                'finishRate' => 40,
            ]],
        ];
        $data = ReflectionUtils::invokeMethod($exporter, 'handleStatistics', [$users, $courseMemberData]);

        $this->assertArrayEquals(['test', '11123455678'."\t", 'test', 'test', 'test', 120, 5, 2, '40%'], $data[0]);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new UserCourseStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $data = $exporter->getContent(0, 10);
        $this->assertEmpty($data);
    }
}
