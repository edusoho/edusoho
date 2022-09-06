<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Service\BehaviorVerificationBlackIpService;
use Biz\BehaviorVerification\Dao\BehaviorVerificationBlackIpDao;

class BehaviorVerificationBlackIpServiceImpl extends BaseService implements BehaviorVerificationBlackIpService
{

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
            $this->getBehaviorVerificationIpDao()->create(['ip' => $ip, 'expire_time' => time() + 3 * 30 * 24 * 3600]);
        }
    }

    /**
     * @return BehaviorVerificationBlackIpDao
     */
    protected function getBehaviorVerificationIpDao()
    {
        return $this->createDao("BehaviorVerification:BehaviorVerificationBlackIpDao");
    }
}
