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
        $refererlog       = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange  = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $summary = $this->getRefererLogService()->searchAnalysisSummary($conditions);

        $this->assertEquals(3, count($summary));
        $this->assertEquals(0, $summary[2]['count']);
    }

    public function testSearchAnalysisSummaryList()
    {
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange  = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $refererlist = $this->getRefererLogService()->searchAnalysisSummaryList($conditions, 'targetId', 0, 2);
        $this->assertEquals(2, count($refererlist));
    }

    public function testSearchAnalysisSummaryListCount()
    {
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog       = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange  = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $refererCount = $this->getRefererLogService()->searchAnalysisSummaryListCount($conditions);
        $this->assertEquals(3, $refererCount);
    }

    public function testSearchAnalysisDetail()
    {
        $course           = $this->createCourse();
        $refererlog1      = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2      = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog2);
        $refererlog3      = $this->moocReferelog($course);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog3);

        $timeRange      = $this->getTimeRange();
        $conditions     = array_merge($timeRange, array('targetType' => 'course', 'targetId' => $course['id']));
        $refererDetails = $this->getRefererLogService()->searchAnalysisDetail($conditions, 'refererHost');
        $this->assertEquals(2, count($refererDetails));
    }

    public function testSearchAnalysisDetailList()
    {
        $course           = $this->createCourse();
        $refererlog1      = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2      = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog2);
        $refererlog3      = $this->moocReferelog($course);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog3);
        $refererlog4      = $this->moocReferelog($course);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog4);
        $timeRange        = $this->getTimeRange();
        $conditions       = array_merge($timeRange, array('targetType' => 'course', 'targetId' => $course['id']));

        $refererDetailList = $this->getRefererLogService()->searchAnalysisDetailList($conditions, 'refererUrl', 0, 5);

        $referercount = 0;
        array_walk($refererDetailList, function ($refererDetail) use (&$referercount) {
            $referercount += $refererDetail['count'];
        });
        $this->assertEquals(2, count($refererDetailList));
        $this->assertEquals(3, $referercount);
    }

    public function testSearchAnalysisDetailListCount()
    {
        $course           = $this->createCourse();
        $refererlog1      = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2      = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog2);
        $refererlog3      = $this->moocReferelog($course);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog3);
        $refererlog4      = $this->moocReferelog($course);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog4);
        $timeRange        = $this->getTimeRange();
        $conditions       = array_merge($timeRange, array('targetType' => 'course', 'targetId' => $course['id']));

        $refererDetailCount = $this->getRefererLogService()->searchAnalysisDetailListCount($conditions, 'refererUrl');

        $this->assertEquals(2, $refererDetailCount);
    }

    public function testSearchRefererLogs()
    {
        $course            = $this->createCourse();
        $refererlog1       = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2       = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);

        $refererLogs = $this->getRefererLogService()->searchRefererLogs(array(), array('createdTime', 'DESC'), 0, 2);

        $this->assertArrayEquals(array($createRefererLog1, $createRefererLog2), $refererLogs);
    }

    public function testSearchRefererCount()
    {
        $course            = $this->createCourse();
        $refererlog1       = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2       = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);

        $count = $this->getRefererLogService()->searchRefererLogCount(array());

        $this->assertEquals(count(array($createRefererLog1, $createRefererLog2)), $count);
    }

    public function testFindRefererLogsGroupByDate()
    {
        $course            = $this->createCourse();
        $date              = date('Y-m-d', time());
        $refererlog1       = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2       = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);
        $groupedLogs       = $this->getRefererLogService()->findRefererLogsGroupByDate(array());

        $this->assertEquals(2, count($groupedLogs[$date]));
    }

    private function createCourse()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        return $this->getCourseService()->createCourse($course);
    }

    private function moocReferelog($course = null, $refererUrl = null)
    {
        $course = empty($course) ? $this->createCourse() : $course;

        $refererlog = array(
            'targetId'   => $course['id'],
            'targetType' => 'course',
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
