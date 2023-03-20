<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Service\BehaviorVerificationBlackIpService;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\BehaviorVerification\Service\SmsRequestLogService;
use Biz\System\Service\LogService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class BehaviorVerificationServiceImpl extends BaseService implements BehaviorVerificationService
{

    public function behaviorVerification($request)
    {
        if ($request->isXmlHttpRequest()) {
            $conditions['fingerprint'] = $request->request->get('encryptedPoint');
            $conditions['userAgent'] = $request->headers->get('user-agent');
            $conditions['ip'] = $request->getClientIp();
            $conditions['mobile'] = $request->get('mobile');
            if ($this->getBehaviorVerificationBlackIpService()->isInBlackIpList($conditions['ip'])) {
                $this->getSmsRequestLogService()->isRobot($conditions);
                return true;
            }

            if ($this->getSmsRequestLogService()->isRobot($conditions)) {
                $this->getBehaviorVerificationBlackIpService()->addBlackIpList($conditions['ip']);
                return true;
            }
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

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return SmsRequestLogService
     */
    protected function getSmsRequestLogService()
    {
        return $this->createService('BehaviorVerification:SmsRequestLogService');
    }
}