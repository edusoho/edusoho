<?php

namespace Biz\SmsBlackIp\Service\Impl;

use Biz\BaseService;
use Biz\SmsBlackIp\Service\SmsBlackIpService;
use Biz\SmsBlackIp\Dao\SmsBlackIpDao;

class SmsBlackIpServiceImpl extends BaseService implements SmsBlackIpService
{

    public function isInBlackIpList($ip)
    {
        $smsBlackIp = $this->getSmsBlackIpDao()->getByIp($ip);
        if (empty($smsBlackIp)){
            return false;
        }
        return true;
    }

    public function addBlackIpList($ip)
    {
        $smsBlackIp = $this->getSmsBlackIpDao()->getByIp($ip);
        if (empty($smsBlackIp)){
            $this->getSmsBlackIpDao()->create(['ip'=>$ip, 'expire_time'=>time() + 3 * 30 * 24 * 3600]);
        }
    }

    /**
     * @return SmsBlackIpDao
     */
    protected function getSmsBlackIpDao()
    {
        return $this->createDao("SmsBlackIp:SmsBlackIpDao");
    }
}
