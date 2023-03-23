<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Dao\BehaviorVerificationBlackIpDao;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\SmsRequestLog\service\SmsRequestLogService;

class BehaviorVerificationServiceImpl extends BaseService implements BehaviorVerificationService
{

    public function behaviorVerification($request)
    {
        if ($request->isXmlHttpRequest()) {
            $smsRequestLog = [];
            $smsRequestLog['fingerprint'] = $request->request->get('encryptedPoint');
            $smsRequestLog['userAgent'] = $request->headers->get('user-agent');
            $smsRequestLog['ip'] = $request->getClientIp();
            $smsRequestLog['mobile'] = $request->get('mobile');
            $ip = $request->getClientIp();
            if ($this->isInBlackIpList($ip)) {
                return true;
            }

            if ($this->getSmsRequestLogService()->isIllegalSmsRequest($smsRequestLog['ip'], $smsRequestLog['fingerprint'])) {
                $this->getSmsRequestLogService()->createSmsRequestLog($smsRequestLog, 1);
                $this->addBlackIpList($ip);

                return true;
            }
            $this->getSmsRequestLogService()->createSmsRequestLog($smsRequestLog, 0);
        }

        return false;
    }

    public function isInBlackIpList($ip)
    {
        $smsBlackIp = $this->getBehaviorVerificationIpDao()->getByIp($ip);
        if (empty($smsBlackIp)) {
            return false;
        }
        if ($smsBlackIp['expire_time'] < time()) {
            return false;
        }
        return true;
    }

    public function addBlackIpList($ip)
    {
        $smsBlackIp = $this->getBehaviorVerificationIpDao()->getByIp($ip);
        if (empty($smsBlackIp)) {
            $this->getBehaviorVerificationIpDao()->create(['ip' => $ip, 'expire_time' => time() + 7 * 24 * 3600]);
        }
    }

    /**
     * @return BehaviorVerificationBlackIpDao
     */
    protected function getBehaviorVerificationIpDao()
    {
        return $this->createDao("BehaviorVerification:BehaviorVerificationBlackIpDao");
    }

    /**
     * @return BehaviorVerificationCoordinateService
     */
    protected function getBehaviorVerificationCoordinateService()
    {
        return $this->createService('BehaviorVerification:BehaviorVerificationCoordinateService');
    }

    /**
     * @return SmsRequestLogService
     */
    protected function getSmsRequestLogService()
    {
        return $this->createService('BehaviorVerification:SmsRequestLogService');
    }
}