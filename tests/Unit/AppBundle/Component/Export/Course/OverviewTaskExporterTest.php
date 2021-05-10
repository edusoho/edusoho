<?php

namespace Tests\Unit\AppBundle\Component\Export\Course;

use AppBundle\Component\Export\Course\OverviewTaskExporter;
use Biz\BaseTestCase;

class OverviewTaskExporterTest extends BaseTestCase
{
    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'getCourse',
                    'returnValue' => [
                        'id' => 1,
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'Course:ReportService',
            [
                [
                    'functionName' => 'getCourseTaskLearnData',
                    'returnValue' => [
                        [
                            'id' => '1',
                            'title' => 'lallll',
                            'finishedNum' => '11',
                            'notStartedNum' => '13',
                            'learnNum' => '14',
                            'rate' => 1,
                        ],
                    ],
                ],
            ]
        );

        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $this->assertTrue(empty($data));

        $result = $expoter->getContent(0, 100);

        $this->assertArrayEquals([
            'lallll',
            '11',
            '14',
            '13',
            '1',
        ], $result[0]);
    }

    public function testBuildCondition()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);
        $result = $expoter->buildCondition([
            'courseId' => 1,
            'alal' => '1123',
            'titleLike' => '1123',
        ]);

        $this->assertArrayEquals([
            'courseId' => 1,
             'titleLike' => '1123',
        ], $result);
    }

    public function testBuildParameter()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);
        $result = $expoter->buildParameter([
            'courseId' => 1,
        ]);

        $this->assertArrayEquals([
            'start' => 0,
            'fileName' => '',
            'courseId' => 1,
        ], $result);
    }

    public function testGetTitles()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $title = [
            'task.learn_data_detail.task_title',
            'task.learn_data_detail.completed_number',
            'task.learn_data_detail.unfinished_number',
            'task.learn_data_detail.unstarted_number',
            'task.learn_data_detail.finished_rate',
        ];

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'countTasks',
                    'returnValue' => 33,
                ],
            ]
        );
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
        ]);

        $this->assertEquals(33, $expoter->getCount());
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
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), [
            'courseId' => 1,
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
