<?php

namespace Tests\Unit\System\Service;

use AppBundle\Common\ReflectionUtils;
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

        $this->getLogService()->info('coin', 'add', 'this is info message', array());
        $logs = $this->getLogService()->searchLogs(array(), 'createdByAsc', 0, 100);
        $this->assertTrue(sizeof($logs) > 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testSearchWithErrorParams()
    {
        $this->getLogService()->searchLogs(array(), 'lastest', 0, 100);
    }

    public function testOldSearch()
    {
        $logs = $this->getLogService()->searchOldLogs(array(), 'created', 0, 100);
        $this->assertTrue(0 == sizeof($logs));

        $logs = $this->getLogService()->searchOldLogs(array(), 'createdByAsc', 0, 100);
        $this->assertTrue(0 == sizeof($logs));
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testOldSearchWithErrorParams()
    {
        $this->getLogService()->searchOldLogs(array(), 'lastest', 0, 100);
    }

    public function testSearchOldLogCount()
    {
        $count = $this->getLogService()->searchOldLogCount(array());
        $this->assertTrue(0 == $count);
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

    public function testPrepareSearchConditions()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($this->getLogService(), 'prepareSearchConditions', array(array(
            'nickname' => 'stu_1',
            'startDateTime' => 1561543220,
            'endDateTime' => 1561553220,
        )));

        $this->assertEquals(strtotime(1561543220), $result['startDateTime']);
        $this->assertEquals(strtotime(1561553220), $result['endDateTime']);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
