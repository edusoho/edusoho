<?php

namespace Tests\Unit\Component\Export\Invite;

use AppBundle\Component\Export\Course\OverviewNormalTaskDetailExporter;
use  Biz\BaseTestCase;

class OverviewNormalTaskDetailExporterTest extends BaseTestCase
{
    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'findUsersByIds',
                    'returnValue' => [
                        1 => [
                            'id' => 1,
                            'nickname' => 'lalala',
                        ],
                    ],
                ],
            ]
        );

        $this->mockBiz(
            'Task:TaskResultService',
            [
                [
                    'functionName' => 'searchTaskResults',
                    'returnValue' => [
                        [
                            'id' => '1',
                            'userId' => 1,
                            'time' => 12,
                            'finishedTime' => 1341,
                            'watchTime' => 13,
                            'createdTime' => 1,
                        ],
                    ],
                ],
            ]
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);

        $result = $expoter->getContent(0, 100);

        $this->assertArrayEquals([
            'lalala'."\t",
            '1970-01-01 08:00:01',
            '1970-01-01 08:22:21',
            '0.2',
            '0.2',
        ], $result[0]);
    }

    public function testBuildCondition()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);
        $result = $expoter->buildCondition([
            'courseTaskId' => 1,
            'alal' => '1123',
            'titleLike' => '1123',
        ]);

        $this->assertArrayEquals([
            'courseTaskId' => 1,
        ], $result);
    }

    public function testBuildParameter()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);
        $result = $expoter->buildParameter([
            'courseTaskId' => 1,
        ]);

        $this->assertArrayEquals([
            'start' => 0,
            'fileName' => '',
            'courseTaskId' => 1,
        ], $result);
    }

    public function testGetTitles()
    {
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);

        $title = [
            'task.learn_data_detail.nickname',
            'task.learn_data_detail.createdTime',
            'task.learn_data_detail.finishedTime',
            'task.learn_data_detail.learnTime',
            'task.learn_data_detail.video_and_audio_learnTime',
        ];

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'countMembers',
                    'returnValue' => 51,
                ],
            ]
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);

        $this->assertEquals(51, $expoter->getCount());
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => false,
                ],
            ]
        );
        $expoter = new OverviewNormalTaskDetailExporter(self::$appKernel->getContainer(), [
            'courseTaskId' => 1,
        ]);

        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions([]);
        $result = $expoter->canExport();
        $this->assertNotTrue($result);

        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ],
            ]
        );
        $result = $expoter->canExport();
        $this->assertTrue($result);
    }
}
