<?php

namespace Biz\BehaviorVerification\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\BehaviorVerification\Dao\SmsBlackListDao;
use Biz\BehaviorVerification\Dao\SmsRequestLogDao;
use Biz\BehaviorVerification\Service\SmsDefenceService;
use Biz\BehaviorVerification\Service\SmsRequestLogService;

class SmsDefenceServiceImpl extends BaseService implements SmsDefenceService
{
    public function validate($fields)
    {
        if ($this->isInBlackIpList($fields['ip'])) {
            return true;
        }

        if ($this->isIllegalIp($fields['ip'])) {
            $fields['disableType'] = ['ip'];
            $fields['isIllegal'] = 1;
            $this->createSmsRequestLog($fields);
            $this->addBlackIpList($fields['ip']);

            return true;
        }

        if ($this->isIllegalCoordinate($fields['fingerprint'])) {
            $fields['disableType'] = ['coordinate'];
            $fields['isIllegal'] = 1;
            $this->createSmsRequestLog($fields);
            $this->addBlackIpList($fields['ip']);

            return true;
        }

        $fields['isIllegal'] = 0;
        $this->createSmsRequestLog($fields);
        return false;
    }

    protected function isInBlackIpList($ip)
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

    protected function addBlackIpList($ip)
    {
        $smsBlackIp = $this->getBehaviorVerificationIpDao()->getByIp($ip);
        if (empty($smsBlackIp)) {
            $this->getBehaviorVerificationIpDao()->create(['ip' => $ip, 'expire_time' => time() + 7 * 24 * 3600]);
        }
    }

    protected function createSmsRequestLog($fields)
    {
        if (!isset($fields['isIllegal'])) {
            throw $this->createServiceException('isIllegal 不能为空');
        }

        $smsRequestLog = [
            'fingerprint' => $fields['fingerprint'] ?? 'empty fingerprint',
            'ip' => $fields['ip'] ?? '',
            'mobile' => $fields['mobile'] ?? '',
            'userAgent' => $fields['userAgent'] ?? '',
        ];

        $smsRequestLog['coordinate'] = $this->decryptCoordinate($smsRequestLog['fingerprint']) ?: 'Illegal Coordinate';
        $smsRequestLog['isIllegal'] = $fields['isIllegal'];

        return $this->getSmsRequestLogDao()->create($smsRequestLog);
    }

    protected function decryptCoordinate($fingerprint)
    {
        global $kernel;
        $csrfToken = $kernel->getContainer()->get('security.csrf.token_manager')->getToken('site');

        return EncryptionToolkit::XXTEADecrypt(base64_decode(mb_substr($fingerprint, 2)), $csrfToken);
    }

    protected function isIllegalIp($ip)
    {
        if (empty($ip)) {
            return true;
        }
        $requestTimesInOneMinute = $this->getSmsRequestLogDao()->count(['ip' => $ip, 'createdTime_GTE' => time() - 60]);
        // todo 10 读取对应的配置文件
        return $requestTimesInOneMinute > 10;
    }

    protected function isIllegalCoordinate($fingerprint)
    {
        if (empty($fingerprint)) {
            return true;
        }
        $existRequestLogs = $this->getSmsRequestLogDao()->search(['fingerprint' => $fingerprint, 'createdTime_GTE' => time() - 60 * 10], null, 0, 10);

        return count($existRequestLogs) > 3;
    }

    /**
     * @return SmsRequestLogDao
     */
    protected function getSmsRequestLogDao()
    {
        return $this->createDao('BehaviorVerification:SmsRequestLogDao');
    }

    /**
     * @return SmsBlackListDao
     */
    protected function getBehaviorVerificationIpDao()
    {
        return $this->createDao('BehaviorVerification:SmsBlackListDao');
    }
}
