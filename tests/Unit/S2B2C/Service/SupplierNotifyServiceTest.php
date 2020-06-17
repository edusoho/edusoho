<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\SupplierNotifyService;
use Biz\System\Service\SettingService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Topxia\Service\Common\ServiceKernel;

class SupplierNotifyServiceTest extends BaseTestCase
{
    public function testOnSiteStatusChange()
    {
        $this->assertNull($this->getSupplierNotifyService()->onSiteStatusChange([]));
    }

    public function testOnCoopModeChange_whenMeError()
    {
        $this->createParameter();
        $this->mockGetMe(['error' => 'Service Error']);
        $result = $this->getSupplierNotifyService()->onCoopModeChange([]);
        $this->assertEquals(['status' => false], $result);
        $this->restoreParameters();
    }

    public function testOnCoopModeChange()
    {
        $this->createParameter();
        $this->mockGetMe();
        $result = $this->getSupplierNotifyService()->onCoopModeChange([]);
        $this->assertEquals(['success' => true], $result);
        $yaml = new Yaml();
        $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $result = $yaml->parseFile($targetPath);
        $this->assertEquals('franchisee', $result['parameters']['school_mode']['business_mode']);
        $this->restoreParameters();
    }

    public function testOnMerchantDomainUrlChange()
    {
        $result = $this->getSupplierNotifyService()->onMerchantDomainUrlChange([]);
        $this->assertNull($result);
    }

    public function testOnSupplierDomainUrlChange()
    {
        $this->createParameter();
        $result = $this->getSupplierNotifyService()->onSupplierDomainUrlChange(['domain_url' => 'new.edusoho.cn']);
        $this->assertEquals(['success' => true], $result);
        $yaml = new Yaml();
        $targetPath = ServiceKernel::instance()->getParameter('kernel.root_dir').'/config/parameters.yml';
        $result = $yaml->parseFile($targetPath);
        $this->assertEquals('new.edusoho.cn', $result['parameters']['school_mode']['supplier']['domain']);
        $this->restoreParameters();
    }

    public function testOnSupplierSiteLogoChange()
    {
        $this->getSettingService()->set('site', [
            'logo' => 'test.com/testlogo.png',
            'favicon' => 'test.com/testfavicon.png',
        ]);
        $result = $this->getSupplierNotifyService()->onSupplierSiteLogoAndFaviconChange([]);
        $this->assertEquals(['success' => true], $result);
        $setting = $this->getSettingService()->get('site');
        $this->assertContains('test.com/testlogo.png', $setting['logo']);
        $this->assertContains('test.com/testfavicon.png', $setting['favicon']);
    }

    public function testOnMerchantAuthNodeChange()
    {
        $this->createParameter();
        $this->mockGetMe(['auth_node' => [
            'logo' => 1,
            'title' => 1,
            'favicon' => 0,
        ]]);

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['s2b2c', []],
                'returnValue' => ['auth_node' => [
                    'logo' => 1,
                    'title' => 1,
                    'favicon' => 0,
                ]],
                'runTimes' => 2,
            ],
            [
                'functionName' => 'set',
                'withParams' => ['s2b2c', ['auth_node' => [
                    'logo' => 1,
                    'title' => 1,
                    'favicon' => 0,
                ]]],
                'returnValue' => [
                ],
                'runTimes' => 1,
            ],
        ]);
        $result = $this->getSupplierNotifyService()->onMerchantAuthNodeChange([]);
        $this->assertEquals(['success' => true], $result);
        $result = $this->getSettingService()->get('s2b2c', []);
        $this->assertEquals(['auth_node' => [
            'logo' => 1,
            'title' => 1,
            'favicon' => 0,
        ]], $result);

        $this->restoreParameters();
    }

    public function testOnResetMerchantBrand()
    {
        $this->createParameter();
        $this->mockGetMe([
            'site_title' => 'test',
            'domain_url',
        ]);
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['site', []],
                'returnValue' => [],
                'runTimes' => 3,
            ],
            [
                'functionName' => 'set',
                'returnValue' => [
                ],
                'runTimes' => 3,
            ],
        ]);
        $result = $this->getSupplierNotifyService()->onResetMerchantBrand([]);
        $this->assertEquals(['success' => true], $result);

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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
