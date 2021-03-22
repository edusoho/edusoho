<?php

namespace Tests\Unit\AppBundle\Component\Export\Course;

use AppBundle\Component\Export\Course\CourseLiveStatisticsExporter;
use Biz\BaseTestCase;

class CourseLiveStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'tryManageCourse', 'returnValue' => [1 => ['id' => 1]]],
        ]);
        $exporter = new CourseLiveStatisticsExporter(self::getContainer(), ['courseId' => 1, 'courseSetId' => 1, 'title' => 'test']);
        self::assertEquals([
            'course.task',
            'course.live_statistics.live_start_time',
            'course.live_statistics.live_time_long',
            'course.live_statistics.max_participate_count',
            'course.live_statistics.live_status',
            'course.live_statistics.check_in_status',
            'course.live_statistics.average_learn_time',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'tryManageCourse', 'returnValue' => [1 => ['id' => 1]]],
        ]);
        $exporter = new CourseLiveStatisticsExporter(self::getContainer(), ['courseId' => 1, 'courseSetId' => 1, 'title' => 'test']);
        $resEmpty = $exporter->getContent(0, 1);
        self::assertEmpty($resEmpty);
    }

    public function testGetCount()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'tryManageCourse', 'returnValue' => [1 => ['id' => 1]]],
        ]);
        $exporter = new CourseLiveStatisticsExporter(self::getContainer(), ['courseId' => 1, 'courseSetId' => 1, 'title' => 'test']);
        $resEmpty = $exporter->getCount();
        self::assertEmpty($resEmpty);
    }
}
