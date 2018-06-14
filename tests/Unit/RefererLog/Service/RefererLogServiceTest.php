<?php

namespace Tests\Unit\RefererLog\Service;

use Biz\BaseTestCase;
use Biz\RefererLog\Service\RefererLogService;

class RefererLogServiceTest extends BaseTestCase
{
    public function testAddRefererLog()
    {
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $this->assertGreaterThan(0, $createRefererLog['id']);
        $this->assertEquals('https://www.baidu.com', $createRefererLog['refererHost']);
    }

    public function testGetRefererLogById()
    {
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererLog = $this->getRefererLogService()->getRefererLogById($createRefererLog['id']);
        $this->assertEquals($refererLog['id'], $createRefererLog['id']);
    }

    public function testAnalysisSummary()
    {
        $course = $this->createCourse();
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course, 'baidu.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course, 'baidu.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course, 'baidu.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, 'weibo.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, 'sina.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course = null, 'qq.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, 'bing.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, 'yaho.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, 'su.com');
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $refererlog = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $summary = $this->getRefererLogService()->analysisSummary($conditions);

        $this->assertEquals(7, count($summary));
        $this->assertEquals(3, $summary[1]['count']);
    }

    public function testSearchAnalysisSummaryList()
    {
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $refererlist = $this->getRefererLogService()->searchAnalysisSummaryList($conditions, 'targetId', 0, 2);

        $this->assertEquals(2, count($refererlist));
    }

    public function testcountDistinctLogsByField()
    {
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog($course = null, $_SERVER['HTTP_HOST']);
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererlog = $this->moocReferelog();
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $timeRange = $this->getTimeRange();
        $conditions = array_merge($timeRange, array('targetType' => 'course'));

        $refererCount = $this->getRefererLogService()->countDistinctLogsByField($conditions, $field = 'targetId');
        $this->assertEquals(3, $refererCount);
        $refererCount = $this->getRefererLogService()->countDistinctLogsByField($conditions, $field = 'refererUrl');
        $this->assertEquals(2, $refererCount);
    }

    public function testSearchRefererLogs()
    {
        $course = $this->createCourse();
        $refererlog1 = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2 = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);

        $refererLogs = $this->getRefererLogService()->searchRefererLogs(array(), array('createdTime' => 'DESC'), 0, 2);

        $this->assertArrayEquals($createRefererLog1, $refererLogs[0]);
        $this->assertArrayEquals($createRefererLog2, $refererLogs[1]);
    }

    public function testSearchRefererCount()
    {
        $course = $this->createCourse();
        $refererlog1 = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);
        $refererlog2 = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);

        $count = $this->getRefererLogService()->countRefererLogs(array());

        $this->assertEquals(count(array($createRefererLog1, $createRefererLog2)), $count);
    }

    public function testFindRefererLogsGroupByDate()
    {
        $course = $this->createCourse();
        $date = date('Y-m-d', time());

        $refererlog1 = $this->moocReferelog();
        $this->getRefererLogService()->addRefererLog($refererlog1);

        $refererlog2 = $this->moocReferelog($course, $_SERVER['HTTP_HOST']);
        $this->getRefererLogService()->addRefererLog($refererlog2);

        $groupedLogs = $this->getRefererLogService()->findRefererLogsGroupByDate(array());

        $this->assertEquals(2, count($groupedLogs[$date]));
    }

    public function testWaveRefererLog()
    {
        $refererlog1 = $this->moocReferelog();
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);

        $this->getRefererLogService()->waveRefererLog($createRefererLog1['id'], 'orderCount', 1);
        $refererLog = $this->getRefererLogService()->getRefererLogById($createRefererLog1['id']);

        $this->assertEquals($createRefererLog1['orderCount'] + 1, $refererLog['orderCount']);
    }

    public function testFindRefererLogsGroupByTargetId()
    {
        $refererlog1 = array(
            'targetId' => 1,
            'targetType' => 'course',
            'refererUrl' => 'http://demo.edusoho.com/course/explore',
            'userAgent' => 'iOS10',
        );
        $createRefererLog1 = $this->getRefererLogService()->addRefererLog($refererlog1);

        $refererlog2 = array(
            'targetId' => 1,
            'targetType' => 'openCourse',
            'refererUrl' => 'http://demo.edusoho.com/open/course/explore',
            'userAgent' => 'iOS10',
        );
        $createRefererLog2 = $this->getRefererLogService()->addRefererLog($refererlog2);

        $refererlog3 = array(
            'targetId' => 1,
            'targetType' => 'openCourse',
            'refererUrl' => 'http://demo.edusoho.com',
            'userAgent' => 'iOS10',
        );
        $createRefererLog3 = $this->getRefererLogService()->addRefererLog($refererlog3);

        $startTime = strtotime(date('Y-m-d', strtotime('-1 day', time())));
        $endTime = strtotime(date('Y-m-d', time()).' 23:59:59');
        $refererLogs = $this->getRefererLogService()->findRefererLogsGroupByTargetId('openCourse', array('hitNum', 'DESC'), $startTime, $endTime, 0, 10);

        $this->assertEquals(1, count($refererLogs));
        $this->assertEquals(2, $refererLogs[0]['hitNum']);
    }

    private function createCourse()
    {
        $course = array(
            'title' => 'online test course 1',
            'type' => 'normal',
            'courseSetId' => '1',
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        );

        return $this->getCourseService()->createCourse($course);
    }

    private function moocReferelog($course = null, $refererUrl = null)
    {
        $course = empty($course) ? $this->createCourse() : $course;

        $refererlog = array(
            'targetId' => $course['id'],
            'targetType' => 'course',
            'refererUrl' => empty($refererUrl) ? 'https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=2&tn=baiduhome_pg&wd=symfony3&rsv_spt=1&oq=sdfsadfsdfsd&rsv_pq=81bbcee100030f47&rsv_t=5b30KXbnTOC01lM%2B7P8apVzBOGbh%2B8ETweQAF1q%2BaFspbHSjNifvQ2ZAdINVnNjpbfcM&rqlang=cn&rsv_enter=1&rsv_sug3=7&rsv_sug1=7&rsv_sug7=100&bs=sdfsadfsdfsd' : $refererUrl,
            'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
        );

        return $refererlog;
    }

    private function getTimeRange()
    {
        return array('startTime' => strtotime(date('Y-m-d', time())), 'endTime' => strtotime(date('Y-m-d', time() + 24 * 3600)));
    }

    /**
     * @return RefererLogService
     */
    protected function getRefererLogService()
    {
        return $this->createService('RefererLog:RefererLogService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
