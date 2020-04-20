<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\SupplierPlatformApi;

class S2B2CFacadeServiceTest extends BaseTestCase
{
    public function testGetMerchantDisabledPermissionList_whenNotCached_thenGetAndCached()
    {
        $disabledPermissions = ['course_set_manage_create'];
        $mockeryPlatformApi = \Mockery::mock(new SupplierPlatformApi($this->biz));
        $mockeryPlatformApi->shouldReceive('getMerchantDisabledPermissionList')->times(1)->andReturn($disabledPermissions);
        $this->biz['supplier.platform_api'] = $mockeryPlatformApi;
        $result = $this->getS2B2CFacadeSercice()->getMerchantDisabledPermissionList();
        $this->assertEquals($disabledPermissions, $result);
    }

    public function testGetMerchantDisabledPermissionList_whenCached_thenGet()
    {
        $disabledPermissions = ['course_set_manage_create', 'course_set_show'];
        $this->mockBiz(
            'System:CacheService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['s2b2c_disabled_permission_list'],
                    'returnValue' => $disabledPermissions,
                ],
            ]
        );
        $result = $this->getS2B2CFacadeSercice()->getMerchantDisabledPermissionList();
        $this->assertEquals($disabledPermissions, $result);
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeSercice()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
