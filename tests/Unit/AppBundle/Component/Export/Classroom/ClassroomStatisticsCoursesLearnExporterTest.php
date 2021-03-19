<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomStatisticsCoursesLearnExporter;
use Biz\BaseTestCase;

class ClassroomStatisticsCoursesLearnExporterTest extends BaseTestCase
{
    public function getTitles()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsCoursesLearnExporter(self::getContainer(), []);
        self::assertEquals([
            '课程名称',
            '任务数',
            '已学完人数',
            '学习中人数',
            '未开始人数',
            '完课率',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsCoursesLearnExporter(self::getContainer(), []);
        $resEmpty = $exporter->getContent(0, 1);
        self::assertEmpty($resEmpty);
    }

    public function testGetCount()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsCoursesLearnExporter(self::getContainer(), []);
        $resEmpty = $exporter->getCount();
        self::assertEmpty($resEmpty);
    }
}
