<?php
namespace Tests\System;

use Topxia\Service\Common\BaseTestCase;

class LogServiceTest extends BaseTestCase
{
    public function testInfo()
    {
        $log = $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $this->assertNotNull($log);
    }

    public function testWarn()
    {
        $log = $this->getLogService()->info('coin', 'add', 'this is warn message', array());
        $this->assertNotNull($log);
    }

    public function testError()
    {
        $log = $this->getLogService()->info('coin', 'add', 'this is error message', array());
        $this->assertNotNull($log);
    }

    public function testSearch()
    {
        $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $logs = $this->getLogService()->search(array(), 'created', 0, 100);
        $this->assertTrue(sizeof($logs) > 1);
    }

    public function testCount()
    {
        $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $count = $this->getLogService()->count(array());
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

    public function testGetLogModuleDicts()
    {
        $dicts = $this->getLogService()->getLogModuleDicts();
        //TODO
    }

    public function testFindLogActionDictsyModule()
    {
        $dicts = $this->getLogService()->findLogActionDictsyModule('coin');
        //TODO
    }

    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
    }

}
