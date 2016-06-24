<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class RefererLogServiceTest extends BaseTestCase
{
    public function testAddRefererLog()
    {
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $this->assertGreaterThan(0, $createRefererLog['id']);
        $this->assertEquals('https://www.baidu.com', $createRefererLog['refererHost']);
    }

    public function testGetRefererLogById()
    {
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererLog       = $this->getRefererLogService()->getRefererLogById($createRefererLog['id']);
        $this->assertEquals($refererLog['id'], $createRefererLog['id']);
    }

    public function testSearchAnalysisSummary()
    {
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog($_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange  = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $summary = $this->getRefererLogService()->searchAnalysisSummary($conditions, 'refererHost');
        $this->assertEquals(2, count($summary));
    }

    private function createCourse()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        return $this->getCourseService()->createCourse($course);
    }

    private function moocReferelog($refererUrl = null)
    {
        $course     = $this->createCourse();
        $refererlog = array(
            'targetId'   => $course['id'],
            'targetType' => 'course',
            'schemeHost' => $_SERVER['HTTP_HOST'],
            'refererUrl' => empty($refererUrl) ? 'https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=2&tn=baiduhome_pg&wd=symfony3&rsv_spt=1&oq=sdfsadfsdfsd&rsv_pq=81bbcee100030f47&rsv_t=5b30KXbnTOC01lM%2B7P8apVzBOGbh%2B8ETweQAF1q%2BaFspbHSjNifvQ2ZAdINVnNjpbfcM&rqlang=cn&rsv_enter=1&rsv_sug3=7&rsv_sug1=7&rsv_sug7=100&bs=sdfsadfsdfsd' : $refererUrl
        );
        return $refererlog;
    }

    private function getTimeRange()
    {
        return array('startTime' => strtotime(date("Y-m-d", time())), 'endTime' => strtotime(date("Y-m-d", time() + 24 * 3600)));
    }

    protected function getRefererLogService()
    {
        return $this->getServiceKernel()->createService('RefererLog.RefererLogService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
