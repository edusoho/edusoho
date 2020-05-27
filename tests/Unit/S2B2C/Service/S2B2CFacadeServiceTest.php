<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;

class S2B2CFacadeServiceTest extends BaseTestCase
{
    public function testGetMerchantDisabledPermissions_whenNotCached_thenGetAndCached()
    {
        $disabledPermissions = ['course_set_manage_create'];
        $mockeryPlatformApi = \Mockery::mock(new SupplierPlatformApi($this->biz));
        $mockeryPlatformApi->shouldReceive('getMerchantDisabledPermissions')->times(1)->andReturn($disabledPermissions);
        $this->biz['supplier.platform_api'] = $mockeryPlatformApi;
        $result = $this->getS2B2CFacadeSercice()->getMerchantDisabledPermissions();
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
        $result = $this->getS2B2CFacadeSercice()->getMerchantDisabledPermissions();
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
        $behaviourPermissions = $this->getS2B2CFacadeSercice()->getBehaviourPermissions();
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
        $behaviourPermissions = $this->getS2B2CFacadeSercice()->getBehaviourPermissions();
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
        $behaviourPermissions = $this->getS2B2CFacadeSercice()->getBehaviourPermissions();
        $this->assertTrue($behaviourPermissions['canModifySiteName']);
        $this->assertFalse($behaviourPermissions['canModifySiteLogo']);
        $this->assertTrue($behaviourPermissions['canModifySiteFavicon']);
        $this->assertFalse($behaviourPermissions['canAddCourse']);
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeSercice()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
