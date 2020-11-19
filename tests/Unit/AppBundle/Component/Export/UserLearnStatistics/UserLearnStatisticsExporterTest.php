<?php

namespace Tests\Unit\Component\Export;

use AppBundle\Common\ReflectionUtils;
use AppBundle\Component\Export\UserLearnStatistics\UserLearnStatisticsExporter;
use Biz\BaseTestCase;

class UserLearnStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $result = [
            'user.learn.statistics.nickname',
            'user.learn.statistics.join.classroom.num',
            'user.learn.statistics.exit.classroom.num',
            'user.learn.statistics.join.course.num',
            'user.learn.statistics.exit.course.num',
            'user.learn.statistics.finished.task.num',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.pure_learn_time',
            'user.learn.statistics.actual.amount',
        ];

        $this->assertArrayEquals($expoter->getTitles(), $result);
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
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $contisions = [];
        $result = $expoter->buildCondition($contisions);
        $this->assertEquals([], $result['userIds']);

        $contisions = ['nickname' => 'la'];

        $result = $expoter->buildCondition($contisions);
        $this->assertEquals(1, $result['userIds'][0]);
        $this->assertNotTrue(isset($result['nickname']));
    }

    public function testCanExport()
    {
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $this->assertEquals(true, $expoter->canExport());

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);

        $this->assertEquals(false, $expoter->canExport());
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
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);
        $count = $expoter->getCount();

        $this->assertEquals(3, $expoter->getCount());
    }

    public function testHandlerStatistics()
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
        $exporter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $users = [
            [
                'id' => 1,
                'nickname' => 'lalal',
            ],
        ];
        $statistics = [
            [
                'userId' => 1,
                'joinedClassroomNum' => 3,
                'exitClassroomNum' => 4,
                'joinedCourseNum' => 35,
                'exitCourseNum' => 13,
                'finishedTaskNum' => 23,
                'learnedSeconds' => 60,
                'actualAmount' => 100,
            ],
        ];
        $data = ReflectionUtils::invokeMethod($exporter, 'handlerStatistics', [$statistics, $users]);

        $this->assertArrayEquals([
            'lalal',
            '3',
            '4',
            '35',
            '13',
            '23',
            '0',
            '0',
            '1',
        ], $data[0]);
    }

    public function testGetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), [
        ]);

        $data = $expoter->getContent(0, 10);
        $this->assertArrayEquals([
            'admin',
            '0',
            '0',
            '0',
            '0',
            '0',
            '0',
            '0',
        ], $data[0]);
    }
}
