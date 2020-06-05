<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\SupplierNotifyService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class SupplierNotifyServiceTest extends BaseTestCase
{
    public function testOnSiteStatusChange()
    {
        $this->assertNull($this->getSupplierNotifyService()->onSiteStatusChange([]));
    }

    public function testOnCoopModeChange()
    {
        $this->createParameter();
        $this->mockGetMe();
        $this->getSupplierNotifyService()->onCoopModeChange([]);
        $yaml = new Yaml();
        $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $result = $yaml->parseFile($targetPath);
        $this->assertEquals('franchisee', $result['parameters']['school_mode']['business_mode']);
        $this->restoreParameters();
    }

    protected function createParameter()
    {
        $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $bakPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml.bak';
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($targetPath)) {
            $fileSystem->copy($targetPath, $bakPath, true);
        }
        $parameters = [
            'parameters' => [
                'database_driver' => 'pdo_mysql',
                'database_host' => '127.0.0.1',
                'database_port' => 3306,
                'database_name' => 'edusoho',
                'database_user' => 'root',
                'database_password' => '',
                'locale' => 'zh_CN',
                'secret' => 'test',
                'school_mode' => [
                    'type' => 'merchant',
                    'business_mode' => 'dealer',
                    'supplier' => [
                        'id' => 1,
                        'domain' => 'test.edusoho.cn',
                    ],
                ],
            ],
        ];
        $yaml = new Yaml();
        $fh = fopen($targetPath, 'w');
        fwrite($fh, $yaml->dump($parameters, 4));
        fclose($fh);
    }

    protected function restoreParameters()
    {
        $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $bakPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml.bak';
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($bakPath)) {
            var_dump(123);
            $fileSystem->copy($bakPath, $targetPath, true);
            $fileSystem->remove($bakPath);
        }
    }

    protected function mockGetMe($return = [])
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['storage', []],
                'returnValue' => [
                    'cloud_access_key' => 'testkey',
                    'cloud_secret_key' => 'testsecret',
                ],
                'runTimes' => 1,
            ],
            [
                'functionName' => 'get',
                'withParams' => ['developer', []],
                'returnValue' => [
                ],
                'runTimes' => 1,
            ],
        ]);
        $mockedS2B2CService = \Mockery::mock($this->biz['qiQiuYunSdk.s2b2cService']);
        $mockedS2B2CService->shouldReceive('getMe')->times(1)->andReturn($return ?: ['coop_mode' => 'franchisee', 'name' => 'test']);
        $this->biz->offsetUnset('qiQiuYunSdk.s2b2cService');
        $this->biz->offsetSet('qiQiuYunSdk.s2b2cService', $mockedS2B2CService);
    }

    /**
     * @return SupplierNotifyService
     */
    protected function getSupplierNotifyService()
    {
        return $this->createService('S2B2C:SupplierNotifyService');
    }
}
