<?php

namespace Tests\Unit\Component\Export\Course;

use AppBundle\Component\Export\Course\LiveStatisticsCheckinListExporter;
use Biz\BaseTestCase;

class LiveStatisticsCheckinListExporterTest extends BaseTestCase
{
    public function testBuildCondition()
    {
        list($exporter, $expected) = $this->initExporter();
        $result = $exporter->buildCondition($expected);

        $this->assertEquals($expected, $result);
    }

    public function testGetTitles()
    {
        list($exporter, $conditions) = $this->initExporter();
        $expected = [
            'user.fields.username_label',
            'course.live_statistics.checkin_status',
        ];

        $this->assertEquals($expected, $exporter->getTitles());
    }

    public function testCanExport_whenThrowException_thenReturnFalse()
    {
        list($exporter, $conditions) = $this->initExporter();
        $this->assertFalse($exporter->canExport());
    }

    public function testCanExport_whenIsAdmin_thenReturnTrue()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'withParams' => [2],
                'returnValue' => [],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertTrue($exporter->canExport());
    }

    public function testCanExport_whenIsAdminAndNotEmptyCourse_thenReturnTrue()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'withParams' => [2],
                'returnValue' => ['id' => 1],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertTrue($exporter->canExport());
    }

    public function testGetCount_returnZero()
    {
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getCheckinStatisticsByLiveId',
                'returnValue' => ['data' => ['detail' => []]],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();
        $this->assertEquals(0, $exporter->getCount());
    }

    public function testGetCount()
    {
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getCheckinStatisticsByLiveId',
                'returnValue' => ['data' => ['detail' => [1, 2, 3]]],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertEquals(3, $exporter->getCount());
    }

    public function testGetContent()
    {
        $this->mockBiz('Live:LiveStatisticsService', [
            [
                'functionName' => 'getCheckinStatisticsByLiveId',
                'returnValue' => ['data' => ['detail' => [
                    [
                        'nickname' => 'testName1',
                        'checkin' => 1,
                    ],
                    [
                        'nickname' => 'testName2',
                        'checkin' => 0,
                    ],
                    [
                        'nickname' => 'testName3',
                        'checkin' => 1,
                    ],
                ]]],
            ],
        ]);

        list($exporter, $conditions) = $this->initExporter();

        $this->assertEquals([
            ['testName2', '未点名'],
            ['testName3', '已点名'],
        ], $exporter->getContent(1, 2));
    }

    protected function initExporter($conditions = [])
    {
        $conditions = array_merge([
            'liveId' => 1,
            'courseId' => 2,
            'taskId' => 3,
        ], $conditions);

        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $exporter = new LiveStatisticsCheckinListExporter(self::$appKernel->getContainer(), $conditions);

        return [$exporter, $conditions];
    }
}
