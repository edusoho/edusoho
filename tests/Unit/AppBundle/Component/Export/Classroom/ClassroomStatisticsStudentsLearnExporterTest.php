<?php

namespace Tests\Unit\AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Classroom\ClassroomStatisticsStudentsLearnExporter;
use Biz\BaseTestCase;

class ClassroomStatisticsStudentsLearnExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsStudentsLearnExporter(self::getContainer(), []);
        self::assertEquals([
            '用户名',
            '加入班级时间',
            '课程累加学习时长（分）',
            '完课率',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsStudentsLearnExporter(self::getContainer(), []);
    }
}
