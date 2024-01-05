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
            '手机号',
            '加入班级时间',
            '课程累加学习时长（分）',
            '完课率',
        ], $exporter->getTitles());
    }

    public function testGetContent()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsStudentsLearnExporter(self::getContainer(), ['orderBy' => 'joinTimeAsc']);
        $resEmpty = $exporter->getContent(0, 1);
        self::assertEmpty($resEmpty);
    }

    public function testGetCount()
    {
        self::getContainer()->set('biz', $this->getBiz());
        $exporter = new ClassroomStatisticsStudentsLearnExporter(self::getContainer(), ['orderBy' => 'joinTimeAsc']);
        $resEmpty = $exporter->getCount();
        self::assertEmpty($resEmpty);
    }
}
