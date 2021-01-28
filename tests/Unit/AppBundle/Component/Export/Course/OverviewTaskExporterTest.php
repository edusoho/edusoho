<?php

namespace Tests\Unit\Component\Export\Invite;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Course\OverviewTaskExporter;

class OverviewTaskExporterTest extends BaseTestCase
{
    public function testgetContent()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Course:ReportService',
            array(
                array(
                    'functionName' => 'getCourseTaskLearnData',
                    'returnValue' => array(
                        array(
                            'id' => '1',
                            'title' => 'lallll',
                            'finishedNum' => '11',
                            'notStartedNum' => '13',
                            'learnNum' => '14',
                            'rate' => 1,
                        ),
                    ),
                ),
            )
        );

        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $this->assertTrue(empty($data));

        $result = $expoter->getContent(0, 100);

        $this->assertArrayEquals(array(
            'lallll',
            '11',
            '14',
            '13',
            '1',
        ), $result[0]);
    }

    public function testBuildCondition()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));
        $result = $expoter->buildCondition(array(
            'courseId' => 1,
            'alal' => '1123',
            'titleLike' => '1123',
        ));

        $this->assertArrayEquals(array(
            'courseId' => 1,
             'titleLike' => '1123',
        ), $result);
    }

    public function testBuildParameter()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));
        $result = $expoter->buildParameter(array(
            'courseId' => 1,
        ));

        $this->assertArrayEquals(array(
            'start' => 0,
            'fileName' => '',
            'courseId' => 1,
        ), $result);
    }

    public function testGetTitles()
    {
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $title = array(
            'task.learn_data_detail.task_title',
            'task.learn_data_detail.completed_number',
            'task.learn_data_detail.unfinished_number',
            'task.learn_data_detail.unstarted_number',
            'task.learn_data_detail.finished_rate',
        );

        $this->assertArrayEquals($title, $expoter->getTitles());
    }

    public function testGetCount()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'countTasks',
                    'returnValue' => 33,
                ),
            )
        );
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $this->assertEquals(33, $expoter->getCount());
    }

    public function testCanExport()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => false,
                ),
            )
        );
        $expoter = new OverviewTaskExporter(self::$appKernel->getContainer(), array(
            'courseId' => 1,
        ));

        $result = $expoter->canExport();
        $this->assertTrue($result);

        $biz = $this->getBiz();
        $user = $biz['user'];
        $user->setPermissions(array());
        $result = $expoter->canExport();
        $this->assertNotTrue($result);

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'tryManageCourse',
                    'returnValue' => true,
                ),
            )
        );
        $result = $expoter->canExport();
        $this->assertTrue($result);
    }
}
