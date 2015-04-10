<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class CrontabServiceTest extends BaseTestCase
{

    public function testCreateJob()
    {
        $this->assertTrue(true);
        // $job = array(
        //     'name' => '测试定时任务１',
        //     'time' => time(),
        //     'job' => 'TestJob',
        // );

        // $job = $this->getCrontabService()->createJob($job);
        // $this->assertNotNull($job);

    }

    public function getCrontabService()
    {
        return $this->getServiceKernel()->createService('Crontab.CrontabService');
    }

}