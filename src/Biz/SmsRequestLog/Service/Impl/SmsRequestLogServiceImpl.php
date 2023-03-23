<?php

namespace Biz\SmsRequestLog\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\SmsRequestLog\Dao\SmsRequestLogDao;
use Biz\SmsRequestLog\Service\SmsRequestLogService;

class SmsRequestLogServiceImpl extends BaseService implements SmsRequestLogService
{
    public function createSmsRequestLog($fields)
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

    public function isIllegalSmsRequest($ip, $fingerprint)
    {
        if (empty($ip)) {
            return true;
        }
        if (empty($fingerprint)) {
            return true;
        }

        if ($this->isIllegalIp($ip) || $this->isIllegalCoordinate($fingerprint)) {
            return true;
        }

        return false;
    }

    protected function isIllegalIp($ip)
    {
        $requestTimesInOneMinute = $this->getSmsRequestLogDao()->count(['ip' => $ip, 'createdTime_GTE' => time() - 60]);
        // todo 10 读取对应的配置文件
        return $requestTimesInOneMinute > 10;
    }

    protected function isIllegalCoordinate($fingerprint)
    {
        $existRequestLogs = $this->getSmsRequestLogDao()->search(['fingerprint' => $fingerprint, 'createdTime_GTE' => time() - 60 * 10], ['id' => 'DESC'], 0, 10);

        return count($existRequestLogs) > 3;
    }

    /**
     * @return SmsRequestLogDao
     */
    protected function getSmsRequestLogDao()
    {
        return $this->createDao('SmsRequestLog:SmsRequestLogDao');
    }
}
