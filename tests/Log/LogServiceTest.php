<?php


namespace Tests\Log;

use Biz\Log\Service\LogService;
use Topxia\Service\Common\BaseTestCase;

class LogServiceTest extends BaseTestCase
{
    public function testInfoLog()
    {
        $info = $this->getLogService()->info('test', 'test_info', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_info',
            'module' => 'test',
            'message'=> 'test_message',
            'level'  => 'info'
        ), $info);
    }

    public function testErrorLog()
    {
        $info = $this->getLogService()->error('test', 'test_error', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_error',
            'module' => 'test',
            'message'=> 'test_message',
            'level'  => 'error'
        ), $info);
    }

    public function testWarningLog()
    {
        $info = $this->getLogService()->warning('test', 'test_warning', 'test_message');
        $this->assertArraySubset(array(
            'action' => 'test_warning',
            'module' => 'test',
            'message'=> 'test_message',
            'level'  => 'warning'
        ), $info);
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('Log:LogService');
    }
}