<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Service\BehaviorVerificationBlackIpService;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;

class BehaviorVerificationServiceImpl extends BaseService implements BehaviorVerificationService
{

    public function behaviorVerification($request)
    {
        $encryptedPoint = $request->request->get('encryptedPoint');
        if ($this->getBehaviorVerificationBlackIpService()->isInBlackIpList($request->getClientIp())){
            return true;
        }
        if($this->getBehaviorVerificationCoordinateService()->isRobot($encryptedPoint)){
            return true;
        }
        return false;
    }

    /**
     * @return BehaviorVerificationCoordinateService
     */
    protected function getBehaviorVerificationCoordinateService()
    {
        return $this->createService('BehaviorVerification:BehaviorVerificationCoordinateService');
    }

    /**
     * @return BehaviorVerificationBlackIpService
     */
    protected function getBehaviorVerificationBlackIpService()
    {
        return $this->createService('BehaviorVerification:BehaviorVerificationBlackIpService');
    }
}