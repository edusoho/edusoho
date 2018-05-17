<?php

namespace Tests\Unit\System\Service;

use Biz\System\Service\LogService;
use Biz\BaseTestCase;

class LogServiceTest extends BaseTestCase
{
    public function testInfoLog()
    {
        $info = $this->getLogService()->info('test', 'test_info', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_info',
            'module' => 'test',
            'message' => 'test_message',
            'level' => 'info',
        ), $info);
    }

    public function testErrorLog()
    {
        $info = $this->getLogService()->error('test', 'test_error', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_error',
            'module' => 'test',
            'message' => 'test_message',
            'level' => 'error',
        ), $info);
    }

    public function testWarningLog()
    {
        $info = $this->getLogService()->warning('test', 'test_warning', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_warning',
            'module' => 'test',
            'message' => 'test_message',
            'level' => 'warning',
        ), $info);
    }

    public function testSearch()
    {
        $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $logs = $this->getLogService()->searchLogs(array(), 'created', 0, 100);
        $this->assertTrue(sizeof($logs) > 1);
    }

    public function testCount()
    {
        $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $count = $this->getLogService()->searchLogCount(array());
        $this->assertTrue($count > 0);
    }

    public function testAnalysisLoginNumByTime()
    {
        $count = $this->getLogService()->analysisLoginNumByTime(time(), time());
        //TODO
    }

    public function testAnalysisLoginDataByTime()
    {
        $data = $this->getLogService()->analysisLoginDataByTime(time(), time());
        //TODO
    }

    public function testGetModules()
    {
        $modules = $this->getLogService()->getModules();

        $this->assertNotEmpty($modules);
    }

    public function testGetActionsByModule()
    {
        $actions = $this->getLogService()->getActionsByModule('course');

        $this->assertNotEmpty($actions);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
