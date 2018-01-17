<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;

class DistributorRegistDecoderImplTest extends BaseTestCase
{
    public function testDealDataBeforeSave()
    {
        $settingService = $this->mockSettingService();
        $token = $this->mockFeatureDistributorToken();

        $result = ReflectionUtils::invokeMethod(
            $this->getDistributorRegisterDecoder(),
            'dealDataBeforeSave',
            array(
                array('distributorToken' => $token), array(),
            )
        );

        $this->assertEquals('distributor', $result['type']);
        $this->assertEquals($token, $result['distributorToken']);

        $settingService->shouldHaveReceived('get')->times(3);
    }

    public function testDealDataAfterSave()
    {
        $settingService = $this->mockSettingService();
        $token = $this->mockFeatureDistributorToken();

        $distributorUserService = $this->mockBiz(
            'Distributor:DistributorUserService',
            array(
                array(
                    'functionName' => 'createJobData',
                    'withParams' => array(array('id' => 1, 'token' => $token)),
                ),
                array(
                    'functionName' => 'decodeToken',
                    'withParams' => array($token),
                    'returnValue' => array(
                        'couponPrice' => 100,
                        'couponExpiryDay' => 1,
                        'registable' => true,
                        'rewardable' => true,
                    ),
                ),
            )
        );

        $couponService = $this->mockBiz(
            'Coupon:CouponService',
            array(
                array(
                    'functionName' => 'generateDistributionCoupon',
                    'withParams' => array(
                        1, 100, 1,
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getDistributorRegisterDecoder(),
            'dealDataAfterSave',
            array(
                array('distributorToken' => $token), array('id' => 1),
            )
        );

        $distributorUserService->shouldHaveReceived('createJobData')->times(1);
        $distributorUserService->shouldHaveReceived('decodeToken')->times(1);
        $couponService->shouldHaveReceived('generateDistributionCoupon')->times(1);

        $this->assertNull($result);
    }

    public function testDealDataAfterSaveWithErrorInLog()
    {
        $logger = $this->mockBiz(
            'logger',
            array(
                array(
                    'functionName' => 'error',
                    'withParams' => array(
                        'distributor sign error DistributorRegistDecoderImpl::dealDataAfterSave',
                        array(
                            'userId' => 1,
                            'token' => 123123,
                        ),
                    ),
                ),
            )
        );
        $this->biz['logger'] = $logger;

        $result = ReflectionUtils::invokeMethod(
            $this->getDistributorRegisterDecoder(),
            'dealDataAfterSave',
            array(
                array('distributorToken' => 123123), array('id' => 1),
            )
        );

        $logger->shouldHaveReceived('error')->times(1);
        $this->assertNull($result);
    }

    protected function getDistributorRegisterDecoder()
    {
        return $this->biz['user.register.distributor'];
    }

    protected function getDistributorUserService()
    {
        return $this->biz->service('Distributor:DistributorUserService');
    }

    private function mockSettingService()
    {
        return $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => '6uVG1xmibb3EX7XhUV3g6jflPidNhNon',
                        'cloud_secret_key' => 'hj4iRrB2DEGAMDRHzVYFed14weSN1gbi',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('developer', array()),
                    'returnValue' => array(),
                ),
            )
        );
    }

    private function mockFeatureDistributorToken()
    {
        TimeMachine::setMockedTime(1515651757);
        $tokenExpireTime = TimeMachine::time() + 172800;  //有效期2天

        $token = $this->getDistributorUserService()->encodeToken(
            array(
                'merchant_id' => '222',
                'agency_id' => '1',
                'coupon_price' => '100',
                'coupon_expiry_day' => '123',
            ), $tokenExpireTime
        );

        return $token;
    }
}
