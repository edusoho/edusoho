<?php

namespace Tests\Unit\AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\UserLearnStatistics\UserLessonStatisticsExporter;
use  Biz\BaseTestCase;

class UserLessonStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $result = [
            'user.learn.statistics.nickname',
            'user.learn.statistics.mobile',
            'classroom.name',
            'course.name',
            'user.learn.statistics.course_name',
            'user.learn.statistics.lesson_name',
            'user.learn.statistics.lesson_type',
            'user.learn.statistics.type',
            'user.learn.statistics.video_length',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.pure_learn_time',
            'user.learn.statistics.lesson.finish_rate',
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
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
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
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
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
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $this->assertEquals(3, $exporter->getCount());
    }

    public function testFindMemberTaskData()
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
                        ['id' => 1, 'title' => 'courseTitle', 'taskNum' => 5],
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
            'Task:TaskService',
            [
                [
                    'functionName' => 'findTasksByCourseIds',
                    'returnValue' => [
                        ['id' => 1, 'title' => 'taskTitle', 'type' => 'text', 'isOptional' => 0, 'courseId' => 1],
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Task:TaskResultService',
            [
                [
                    'functionName' => 'findTaskResultsByUserId',
                    'returnValue' => [
                        [],
                    ],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'findTaskResultsByUserId',
                    'returnValue' => [
                        ['courseTaskId' => 1, 'finishedTime' => time()],
                    ],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz(
            'Visualization:ActivityLearnDataService',
            [
                [
                    'functionName' => 'searchActivityLearnDailyData',
                    'returnValue' => [
                        ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300, 'pureTime' => 120],
                        ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300, 'pureTime' => 120],
                        ['userId' => 2, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'sumTime' => 300, 'pureTime' => 120],
                    ],
                ],
            ]
        );

        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $courseMembers = [
            ['userId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'learnedNum' => 3, 'classroomId' => 1],
            ['userId' => 2, 'courseId' => 1, 'courseSetId' => 1, 'learnedNum' => 5, 'classroomId' => 0],
        ];
        $data = ReflectionUtils::invokeMethod($exporter, 'findMemberTaskData', [$courseMembers]);

        $this->assertEquals('未完成', $data[1][0]['finishStatus']);
        $this->assertEquals('classroomTitle', $data[1][0]['classroomName']);
        $this->assertEquals('完成', $data[2][0]['finishStatus']);
        $this->assertEquals('', $data[2][0]['classroomName']);
    }

    public function testHandleStatistics()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $users = [['id' => 1, 'nickname' => 'test', 'verifiedMobile' => '11123455678']];
        $memberTaskData = [
            '1' => [[
                'classroomName' => 'test',
                'courseSetName' => 'test',
                'courseName' => 'test',
                'taskName' => 'test',
                'taskType' => '图文课时',
                'type' => '必修',
                'length' => 0,
                'sumTime' => 120,
                'pureTime' => 60,
                'finishStatus' => '完成',
            ]],
        ];
        $data = ReflectionUtils::invokeMethod($exporter, 'handleStatistics', [$users, $memberTaskData]);

        $this->assertArrayEquals(['test', '11123455678'."\t", 'test', 'test', 'test', 'test', '图文课时', '必修', 0, 120, 60, '完成'], $data[0]);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new UserLessonStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $data = $exporter->getContent(0, 10);
        $this->assertEmpty($data);
    }
}
