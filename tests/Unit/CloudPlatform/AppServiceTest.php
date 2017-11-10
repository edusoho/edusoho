<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\Client\EduSohoAppClient;
use Mockery;

class AppServiceTest extends BaseTestCase
{
    public function testGetAppByCode()
    {
        $name = 'code1';
        $app = $this->_createApp($name);

        $result = $this->getAppService()->getAppByCode($name);

        $this->assertArrayEquals($app, $result);
    }

    public function testFindApps()
    {
        $this->_createApp('code1');
        $this->_createApp('code2');
        $this->_createApp('code3');

        $results = $this->getAppService()->findApps(0, 3);

        $this->assertEquals(3, count($results));
    }

    public function testFindAppCount()
    {
        $this->_createApp('code1');
        $this->_createApp('code2');
        $this->_createApp('code3');

        $count = $this->getAppService()->findAppCount();

        $this->assertEquals(3, $count);
    }

    public function testFindAppsByCodes()
    {
        $app1 = $this->_createApp('code1');
        $app2 = $this->_createApp('code2');
        $app3 = $this->_createApp('code3');

        $codes = array($app1['code'], $app3['code']);
        $apps = $this->getAppService()->findAppsByCodes($codes);

        $this->assertArrayEquals($app1, $apps[$app1['code']]);
        $this->assertArrayEquals($app3, $apps[$app3['code']]);
    }

    public function testGetCenterApps()
    {
        $this->mockAppClient();
        $apps = $this->getAppService()->getCenterApps();

        $this->assertEquals('cloudApp', $apps[0]['code']);
    }

    public function testGetBinded()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->getBinded();

        $this->assertEquals(1, $result);
    }

    public function testGetCenterPackageInfo()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->getCenterPackageInfo(1);

        $this->assertEquals('MAIN', $result['product']['code']);
    }

    public function testGetMainVersion()
    {
        $app = $this->_createApp('MAIN');

        $version = $this->getAppService()->getMainVersion();

        $this->assertEquals($app['version'], $version);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testRegisterAppInvalidArgument()
    {
        $app = array(
            'code' => 'code1',
            'version' => '1.0.0',
            'description' => 'app description',
        );

        $this->getAppService()->registerApp($app);
    }

    public function testRegisterAppNotExist()
    {
        $app = array(
            'code' => 'code1',
            'name' => 'phpunit app test',
            'version' => '1.0.0',
            'description' => 'app description',
        );

        $result = $this->getAppService()->registerApp($app);

        $this->assertEquals($app['code'], $result['code']);
        $this->assertEquals($app['name'], $result['name']);
        $this->assertEquals($app['version'], $result['version']);
        $this->assertEquals($app['description'], $result['description']);
    }

    public function testRegisterAppExist()
    {
        $app = array(
            'code' => 'code1',
            'name' => 'phpunit app test',
            'version' => '1.0.0',
            'description' => 'app description',
        );
        $this->getAppService()->registerApp($app);

        $updateApp = array(
            'code' => 'code1',
            'name' => 'phpunit app test update',
            'version' => '2.0.0',
            'description' => 'app description update',
        );
        $result = $this->getAppService()->registerApp($updateApp);

        $this->assertEquals($updateApp['code'], $result['code']);
        $this->assertEquals($updateApp['name'], $result['name']);
        $this->assertEquals($updateApp['version'], $result['version']);
        $this->assertEquals($updateApp['description'], $result['description']);
    }

    public function testCheckAppUpgrades()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->checkAppUpgrades();

        $this->assertEquals(1, $result);
    }

    public function testGetMessages()
    {
        $this->mockAppClient();

        $result = $this->getAppService()->getMessages();

        $this->assertEquals('message', $result);
    }

    public function testFindLogs()
    {
        $this->_createLog('code1');
        $this->_createLog('code2');
        $this->_createLog('code3');

        $logs = $this->getAppService()->findLogs(0, 3);

        $this->assertEquals(3, count($logs));
    }

    public function testFindLogCount()
    {
        $this->_createLog('code1');
        $this->_createLog('code2');
        $this->_createLog('code3');

        $count = $this->getAppService()->findLogCount();

        $this->assertEquals(3, $count);
    }

    public function testHasLastErrorForPackageUpdate()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->hasLastErrorForPackageUpdate(1);

        $this->assertFalse($result);
    }

    public function testCheckEnvironmentForPackageUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->checkEnvironmentForPackageUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testCheckDependsForPackageUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->checkDependsForPackageUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testBackupDbForPackageUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->backupDbForPackageUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testBackupFileForPackageUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->backupFileForPackageUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testDownloadPackageForUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->downloadPackageForUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testCheckDownloadPackageForUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->checkDownloadPackageForUpdate(1);

        $this->assertArrayEquals(array(), $errors);
    }

    public function testBeginPackageUpdate()
    {
        $this->mockAppClient();
        $errors = $this->getAppService()->beginPackageUpdate(1, 'upgrade');

        $this->assertArrayEquals(array(), $errors);
    }

    public function testRepairProblem()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->repairProblem('token');

        $this->assertEquals('problem', $result);
    }

    public function testFindInstallApp()
    {
        $app1 = $this->_createApp('MAIN');
        $app2 = $this->_createApp('code1');

        $result = $this->getAppService()->findInstallApp('MAIN');

        $this->assertArrayEquals($app1, $result);
    }

    public function testUninstallApp()
    {
        $code = 'MAIN';
        $this->_createApp($code);
        $this->getAppService()->uninstallApp($code);

        $code = $this->getAppService()->getAppByCode($code);

        $this->assertNull($code);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUninstallAppNotExist()
    {
        $this->getAppService()->uninstallApp('MAIN');
    }

    public function testUpdateAppVersion()
    {
        $app = $this->_createApp('MAIN');

        $result = $this->getAppService()->updateAppVersion($app['id'], '8.0.0');

        $this->assertEquals($app['id'], $result['id']);
        $this->assertEquals('8.0.0', $result['version']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUpdateAppVersionNotExist()
    {
        $this->getAppService()->updateAppVersion(5, '8.0.0');
    }

    public function testGetTokenLoginUrl()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->getTokenLoginUrl('course', array());

        $this->assertEquals('loginUrl', $result);
    }

    public function testGetAppStatusByCode()
    {
        $this->mockAppClient();
        $result = $this->getAppService()->getAppStatusByCode('MAIN');

        $this->assertEquals('status', $result);
    }

    private function mockAppClient()
    {
        $client = new EduSohoAppClient(array());
        $mockObject = Mockery::mock($client);

        $mockObject->shouldReceive('getApps')->times(1)->andReturn(array(array('name' => 'cloud app', 'code' => 'cloudApp', 'description' => '')));
        $mockObject->shouldReceive('getBinded')->times(1)->andReturn(1);
        $mockObject->shouldReceive('getPackage')->times(1)->andReturn(array('product' => array('code' => 'MAIN', 'name' => 'MAIN'), 'id' => 1, 'productId' => 1, 'type' => 'upgrade', 'toVersion' => '8.0.0'));
        $mockObject->shouldReceive('checkUpgradePackages')->times(1)->andReturn(1);
        $mockObject->shouldReceive('getMessages')->times(1)->andReturn('message');
        $mockObject->shouldReceive('downloadPackage')->times(1)->andReturn('message');
        $mockObject->shouldReceive('repairProblem')->times(1)->andReturn('problem');
        $mockObject->shouldReceive('getLoginToken')->times(1)->andReturn('token');
        $mockObject->shouldReceive('getTokenLoginUrl')->times(1)->andReturn('loginUrl');
        $mockObject->shouldReceive('getAppStatusByCode')->times(1)->andReturn('status');

        $this->getAppService()->setAppClient($mockObject);
    }

    private function _createApp($code)
    {
        $app = array(
            'code' => $code,
            'name' => 'phpunit app test',
            'version' => '1.0.0',
            'description' => 'app description',
        );

        return $this->getAppService()->registerApp($app);
    }

    private function _createLog($code)
    {
        $package = array(
            'code' => $code,
            'name' => 'main app',
            'fromVersion' => '1.0.0',
            'toVersion' => '1.1.0',
            'type' => 'install',
            'status' => 'SUCCESS',
            'userId' => 1,
            'ip' => '127.0.0.1',
            'message' => '',
            'createdTime' => time(),
            'dbBackupPath' => '',
            'sourceBackupPath' => '',
        );

        $result = $this->getAppLogDao()->create($package, 'SUCCESS', '');

        $this->assertEquals($package['fromVersion'], $result['fromVersion']);
        $this->assertEquals($package['toVersion'], $result['toVersion']);
        $this->assertEquals($package['type'], $result['type']);
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
