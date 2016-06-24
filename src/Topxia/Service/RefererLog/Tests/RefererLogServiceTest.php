<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;

class RefererLogServiceTest extends BaseTestCase
{
    public function testAddRefererLog()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $refererlog = array(
            'targetId'   => $createCourse['id'],
            'targetType' => 'coruse',
            'schemeHost' => $_SERVER['HTTP_HOST'],
            'refererUrl' => 'https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=2&tn=baiduhome_pg&wd=symfony3&rsv_spt=1&oq=sdfsadfsdfsd&rsv_pq=81bbcee100030f47&rsv_t=5b30KXbnTOC01lM%2B7P8apVzBOGbh%2B8ETweQAF1q%2BaFspbHSjNifvQ2ZAdINVnNjpbfcM&rqlang=cn&rsv_enter=1&rsv_sug3=7&rsv_sug1=7&rsv_sug7=100&bs=sdfsadfsdfsd'

        );
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);

        $this->assertGreaterThan(0, $createRefererLog['id']);
        $this->assertEquals('https://www.baidu.com', $createRefererLog['refererHost']);
    }

    public function testGetRefererLogById()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $refererlog = array(
            'targetId'   => $createCourse['id'],
            'targetType' => 'coruse',
            'schemeHost' => $_SERVER['HTTP_HOST'],
            'refererUrl' => 'https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=2&tn=baiduhome_pg&wd=symfony3&rsv_spt=1&oq=sdfsadfsdfsd&rsv_pq=81bbcee100030f47&rsv_t=5b30KXbnTOC01lM%2B7P8apVzBOGbh%2B8ETweQAF1q%2BaFspbHSjNifvQ2ZAdINVnNjpbfcM&rqlang=cn&rsv_enter=1&rsv_sug3=7&rsv_sug1=7&rsv_sug7=100&bs=sdfsadfsdfsd'
        );
        $createRefererLog = $this->getRefererLogService()->addRefererLog($refererlog);
        $refererLog       = $this->getRefererLogService()->getRefererLogById($createRefererLog['id']);
        $this->assertEquals($refererLog['id'], $createRefererLog['id']);
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
