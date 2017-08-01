<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Service\ReportService;

class ReportServiceTest extends BaseTestCase
{
    public function testGetCompletionRateTrend()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1, 'studentNum' => 10)),
        ));

        $result = $this->getReportService()->getCompletionRateTrend(1, '2017-07-01', '2017-07-10');

        $this->assertCount(10, $result);
    }

    public function testGetStudentTrend()
    {
        $result = $this->getReportService()->getStudentTrend(1, array('startDate' => '2017-07-01', 'endDate' => '2017-07-10'));
        $this->assertCount(10, $result);
    }

    /**
     * @return ReportService
     */
    private function getReportService()
    {
        return $this->createService('Course:ReportService');
    }
}
