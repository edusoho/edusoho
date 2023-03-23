<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Dao\BehaviorVerificationBlackIpDao;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\SmsRequestLog\Service\SmsRequestLogService;

class BehaviorVerificationServiceImpl extends BaseService implements BehaviorVerificationService
{
    public function behaviorVerification($request)
    {
        if ($request->isXmlHttpRequest()) {
            $fingerprint = $request->request->get('encryptedPoint');
            $ip = $request->getClientIp();
            $fields = [
                'fingerprint' => $fingerprint,
                'userAgent' => $request->headers->get('user-agent'),
                'ip' => $ip,
                'mobile' => $request->get('mobile')?:$request->get('to'),
            ];
            if ($this->isInBlackIpList($ip)) {
                return true;
            }

            if ($this->getSmsRequestLogService()->isIllegalSmsRequest($ip, $fingerprint)) {
                $fields['isIllegal'] = 1;
                $this->getSmsRequestLogService()->createSmsRequestLog($fields);
                $this->addBlackIpList($ip);

                return true;
            }

            $fields['isIllegal'] = 0;
            $this->getSmsRequestLogService()->createSmsRequestLog($fields);
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
        return $this->createDao('BehaviorVerification:BehaviorVerificationBlackIpDao');
    }

    /**
     * @return SmsRequestLogService
     */
    protected function getSmsRequestLogService()
    {
        return $this->createService('SmsRequestLog:SmsRequestLogService');
    }
}
