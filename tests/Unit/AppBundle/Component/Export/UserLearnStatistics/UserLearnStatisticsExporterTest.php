<?php

namespace Tests\Unit\Component\Export;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Course\UserLearnStatistics;
use AppBundle\Component\Export\UserLearnStatistics\UserLearnStatisticsExporter;

class UserLearnStatisticsExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        $expoter = new UserLearnStatisticsExporter(self::$appKernel->getContainer(), array(
        ));

        $result = array('user.learn.statistics.nickname', 'user.learn.statistics.join.classroom.num', 'user.learn.statistics.exit.classroom.num', 'user.learn.statistics.join.course.num', 'user.learn.statistics.exit.course.num', 'user.learn.statistics.finished.task.num', 'user.learn.statistics.learned.econds', 'user.learn.statistics.actual.amount');

        $this->assertArrayEquals($expoter->getTitles(), $result);
    }
}