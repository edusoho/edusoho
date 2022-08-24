<?php

namespace ApiBundle\Api\Resource\Mytest;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\SmsBlackCoordinate\Service\BehaviorVerificationCoordinateService;
use Biz\SmsBlackIp\Service\BehaviorVerificationIpService;

class Mytest extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        var_dump("hello");
        return $this->getSmsBlackCoordinate()->isRobot("12,24");
//        $this->getSmsBlackIp()->addBlackIpList("122.34.5.23");
//        return $this->getSmsBlackIp()->isInBlackIpList("122.34.5.23");
    }

    /**
     * @return BehaviorVerificationCoordinateService
     */
    protected function getSmsBlackCoordinate()
    {
        return $this->service("BehaviorVerification:BehaviorVerificationCoordinateService");
    }

    /**
     * @return BehaviorVerificationIpService
     */
    protected function getSmsBlackIp()
    {
        return $this->service("BehaviorVerification:BehaviorVerificationIpService");
    }
}