<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\LoginRecordService;
use Topxia\Service\User\UserService;

class LoginRecordServiceTest extends BaseTestCase
{

    public function testSearchLoginRecordCount()
    {

       $this->getLogService()->info('login', 'now', time());
       $this->getLogService()->info('login', 'now', time());
       $this->getLogService()->info('login', 'now', time());
       $loginRecordsCount = $this->getLoginRecordService()->searchLoginRecordCount(
       	array(
       	'nickname' => 'admin',
       	'email'=>'admin@admin.com',
       	'startDateTime'=>'-5 hours',
       	'endDateTime'=>'-2 hours'));
       $this->assertEquals(0, $loginRecordsCount);

       $loginRecordsCount = $this->getLoginRecordService()->searchLoginRecordCount(
       	array(
   		'nickname' => 'admin',
       	'email'=>'admin@admin.com',
       	'startDateTime'=>'-5 hours',
       	'endDateTime'=>'+1 hours'));
       $this->assertEquals(3, $loginRecordsCount);

    }

    public function testSearchLoginRecord()
    {
    	$currentUser = $this->getCurrentUser();
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $loginRecords = $this->getLoginRecordService()->searchLoginRecord(
	       	array(
		       	'nickname' => 'admin',
		       	'email'=>'admin@admin.com',
		       	'startDateTime'=>'-5 hours',
		       	'endDateTime'=>'-2 hours'
	       	),
	       	array('createdTime', 'DESC'),
	       	0,30);
       $this->assertEmpty($loginRecords);

       $loginRecords = $this->getLoginRecordService()->searchLoginRecord(
       			array(
		       	'nickname' => 'admin',
		       	'email'=>'admin@admin.com',
		       	'startDateTime'=>'-5 hours',
		       	'endDateTime'=>'+2 hours'
	       	),
	       	array('createdTime', 'DESC'),
	       	0,30);

		foreach ($loginRecords as $key => $value) {
			$this->assertEquals($currentUser['id'], $value['userId']);
			$this->assertEquals('login', $value['module']);
			$this->assertEquals('action', $value['action']);
			$this->assertEquals('message', $value['message']);
			$this->assertEquals('127.0.0.1', $value['ip']);
			$this->assertEquals('info', $value['level']);
			$this->assertEquals('INNA', $value['location']);
			$this->assertEmpty($value['data']);
		}
    }

    public function testFindLoginRecordCountByUserId()
    {
       $currentUser = $this->getCurrentUser();
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $loginRecordCount = $this->getLoginRecordService()->findLoginRecordCountByUserId($currentUser['id']);
       $this->assertEquals(3, $loginRecordCount);
    }

   	/**
	* @expectedException Topxia\Service\Common\ServiceException
	* @expectedExceptionMessage    ERROR! The User Not Exist!
    */
    public function testFindLoginRecordCountByUserIdWithNotExistUserId()
    {
       $currentUser = $this->getCurrentUser();
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLoginRecordService()->findLoginRecordCountByUserId(99);
    }

    /**
    * @group current 
    */
    public function testFindLoginRecordByUserId()
    {
       $currentUser = $this->getCurrentUser();
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $this->getLogService()->info('login', 'action', 'message');
       $loginRecords = $this->getLoginRecordService()->findLoginRecordByUserId($currentUser['id'], 0, 30);
       foreach ($loginRecords as $key => $value) {
			$this->assertGreaterThan(0, $value['id']);
			$this->assertEquals($currentUser['id'], $value['userId']);
			$this->assertEquals('login', $value['module']);
			$this->assertEquals('action', $value['action']);
			$this->assertEquals('message', $value['message']);
			$this->assertEquals('127.0.0.1', $value['ip']);
			$this->assertEquals('info', $value['level']);
			$this->assertEquals('INNA', $value['location']);
			$this->assertEmpty($value['data']);
       }
    }

    private function getLoginRecordService()
    {
        return $this->getServiceKernel()->createService('User.LoginRecordService');
    }

    private function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');        
    }

}