<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;

class S2B2CFacadeServiceTest extends BaseTestCase
{
    /**
     * getMe做了单例，所以SettingService 只会触发一次
     */
    public function testGetMeTwice()
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
        $mockedS2B2CService->shouldReceive('getMe')->times(1)->andReturn(['id' => 1, 'name' => 'test']);
        $this->biz->offsetUnset('qiQiuYunSdk.s2b2cService');
        $this->biz->offsetSet('qiQiuYunSdk.s2b2cService', $mockedS2B2CService);
        $result = $this->getS2B2CFacadeService()->getMe();
        $this->assertEquals(['id' => 1, 'name' => 'test'], $result);
        $resultTwice = $this->getS2B2CFacadeService()->getMe();
        $this->assertEquals(['id' => 1, 'name' => 'test'], $resultTwice);
    }

    /**
     * getMe虽然做了单例，但是报错的时候SettingService 每次调用都会触发一次，调用两次*2
     */
    public function testGetMeTwice_withError()
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['storage', []],
                'returnValue' => [
                    'cloud_access_key' => 'testkey',
                    'cloud_secret_key' => 'testsecret',
                ],
                'runTimes' => 2,
            ],
            [
                'functionName' => 'get',
                'withParams' => ['developer', []],
                'returnValue' => [
                ],
                'runTimes' => 2,
            ],
        ]);
        $mockedS2B2CService = \Mockery::mock($this->biz['qiQiuYunSdk.s2b2cService']);
        $mockedS2B2CService->shouldReceive('getMe')->times(1)->andReturn(['error' => 'Service unavailable.']);
        $this->biz->offsetUnset('qiQiuYunSdk.s2b2cService');
        $this->biz->offsetSet('qiQiuYunSdk.s2b2cService', $mockedS2B2CService);
        $result = $this->getS2B2CFacadeService()->getMe();
        $this->assertEquals(['error' => 'Service unavailable.'], $result);
        $resultTwice = $this->getS2B2CFacadeService()->getMe();
        $this->assertEquals(['error' => 'Service unavailable.'], $resultTwice);
    }

    /**
     * getSupplier做了单例，所以SettingService 只会触发一次
     */
    public function testGetSupplierTwice()
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
        $mockedS2B2CService->shouldReceive('getOwnSupplier')->times(1)->andReturn(['id' => 1, 'name' => 'test']);
        $this->biz->offsetUnset('qiQiuYunSdk.s2b2cService');
        $this->biz->offsetSet('qiQiuYunSdk.s2b2cService', $mockedS2B2CService);
        $result = $this->getS2B2CFacadeService()->getSupplier();
        $this->assertEquals(['id' => 1, 'name' => 'test'], $result);
        $resultTwice = $this->getS2B2CFacadeService()->getSupplier();
        $this->assertEquals(['id' => 1, 'name' => 'test'], $resultTwice);
    }

    /**
     * getSupplier做了单例，所以SettingService 只会触发一次
     */
    public function testGetSupplierTwice_withError()
    {
        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'withParams' => ['storage', []],
                'returnValue' => [
                    'cloud_access_key' => 'testkey',
                    'cloud_secret_key' => 'testsecret',
                ],
                'runTimes' => 2,
            ],
            [
                'functionName' => 'get',
                'withParams' => ['developer', []],
                'returnValue' => [
                ],
                'runTimes' => 2,
            ],
        ]);
        $mockedS2B2CService = \Mockery::mock($this->biz['qiQiuYunSdk.s2b2cService']);
        $mockedS2B2CService->shouldReceive('getOwnSupplier')->times(1)->andReturn(['error' => 'Service unavailable.']);
        $this->biz->offsetUnset('qiQiuYunSdk.s2b2cService');
        $this->biz->offsetSet('qiQiuYunSdk.s2b2cService', $mockedS2B2CService);
        $result = $this->getS2B2CFacadeService()->getSupplier();
        $this->assertEquals(['error' => 'Service unavailable.'], $result);
        $resultTwice = $this->getS2B2CFacadeService()->getSupplier();
        $this->assertEquals(['error' => 'Service unavailable.'], $resultTwice);
    }

    public function testGetMerchantDisabledPermissions_whenNotCached_thenGetAndCached()
    {
        $disabledPermissions = ['course_set_manage_create'];
        $mockeryPlatformApi = \Mockery::mock(new SupplierPlatformApi($this->biz));
        $mockeryPlatformApi->shouldReceive('getMerchantDisabledPermissions')->times(1)->andReturn($disabledPermissions);
        $this->biz['supplier.platform_api'] = $mockeryPlatformApi;
        $result = $this->getS2B2CFacadeService()->getMerchantDisabledPermissions();
        $this->assertEquals($disabledPermissions, $result);
    }

    public function testGetMerchantDisabledPermissions_whenCached_thenGet()
    {
        $disabledPermissions = ['course_set_manage_create', 'course_set_show'];
        $this->mockBiz(
            'System:CacheService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['s2b2c_disabled_permissions'],
                    'returnValue' => $disabledPermissions,
                ],
                [
                    'functionName' => 'set',
                    'withParams' => ['s2b2c_disabled_permissions', $disabledPermissions],
                    'returnValue' => true,
                ],
            ]
        );
        $result = $this->getS2B2CFacadeService()->getMerchantDisabledPermissions();
        $this->assertEquals($disabledPermissions, $result);
    }

    public function testGetBehaviourPermissions_whenS2B2CDisable_thenHasPermissions()
    {
        $this->biz['s2b2c.config'] = [
            'enabled' => false,
            'supplierId' => null,
            'supplierDomain' => null,
            'businessMode' => null,
        ];
        $behaviourPermissions = $this->getS2B2CFacadeService()->getBehaviourPermissions();
        $this->assertTrue($behaviourPermissions['canModifySiteName']);
        $this->assertTrue($behaviourPermissions['canAddCourse']);
    }

    public function testGetBehaviourPermissions_whenS2B2CEnableAndDealer_thenHasPermissions()
    {
        $this->biz['s2b2c.config'] = [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'www.supplier.com',
            'businessMode' => S2B2CFacadeService::DEALER_MODE,
        ];
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['s2b2c', []],
                    'returnValue' => [
                        'auth_node' => [
                            'title' => 1,
                            'logo' => 0,
                            'favicon' => 1,
                        ],
                    ],
                ],
            ]
        );
        $behaviourPermissions = $this->getS2B2CFacadeService()->getBehaviourPermissions();
        $this->assertTrue($behaviourPermissions['canModifySiteName']);
        $this->assertTrue($behaviourPermissions['canModifySiteLogo']);
        $this->assertTrue($behaviourPermissions['canModifySiteFavicon']);
        $this->assertTrue($behaviourPermissions['canAddCourse']);
    }

    public function testGetBehaviourPermissions_whenS2B2CEnableAndFranchisee_thenHasPermissions()
    {
        $this->biz['s2b2c.config'] = [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'www.supplier.com',
            'businessMode' => S2B2CFacadeService::FRANCHISEE_MODE,
        ];
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['s2b2c', []],
                    'returnValue' => [
                        'auth_node' => [
                            'title' => 1,
                            'logo' => 0,
                            'favicon' => 1,
                        ],
                    ],
                ],
            ]
        );
        $behaviourPermissions = $this->getS2B2CFacadeService()->getBehaviourPermissions();
        $this->assertTrue($behaviourPermissions['canModifySiteName']);
        $this->assertFalse($behaviourPermissions['canModifySiteLogo']);
        $this->assertTrue($behaviourPermissions['canModifySiteFavicon']);
        $this->assertFalse($behaviourPermissions['canAddCourse']);
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
