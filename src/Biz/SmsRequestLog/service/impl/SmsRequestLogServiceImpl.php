<?php

namespace Biz\SmsRequestLog\service\impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\SmsRequestLog\SmsRequestException;
use Biz\SmsRequestLog\Dao\SmsRequestLogDao;
use Biz\SmsRequestLog\service\SmsRequestLogService;

class SmsRequestLogServiceImpl extends BaseService implements SmsRequestLogService
{
    public function createSmsRequestLog($smsRequestLog, $isIllegal)
    {
        if (!ArrayToolkit::requireds($smsRequestLog, ['fingerprint', 'ip', 'mobile', 'userAgent'])) {
            throw $this->createServiceException('smsRequestLog 参数不能为空');
        }
        if (empty($isIllegal)) {
            throw $this->createServiceException('isIllegal 不能为空');
        }
        $smsRequestLog['coordinate'] = $this->decryptCoordinate($smsRequestLog['fingerprint']);
        if (empty($smsRequestLog['coordinate'])) {
            $this->createNewException(SmsRequestException::GET_COORDINATE_FAILED);
        }
        $smsRequestLog['is_illegal'] = $isIllegal;
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
            throw $this->createServiceException('ip 不能为空');
        }
        if (empty($fingerprint)) {
            throw $this->createServiceException('fingerprint 不能为空');
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
        return $this->createDao('BehaviorVerification:SmsRequestLogDao');
    }
}