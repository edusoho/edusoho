<?php

namespace Biz\SmsRequestLog\service\impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\SmsRequestLog\SmsRequestException;
use Biz\SmsRequestLog\Dao\SmsRequestLogDao;
use Biz\SmsRequestLog\service\SmsRequestLogService;

class SmsRequestLogServiceImpl extends BaseService implements SmsRequestLogService
{

    public function isIllegalSmsRequest($conditions)
    {
        $this->validateConditions($conditions);
        $conditions['is_illegal'] = '1';
        $conditions['coordinate'] = $this->decryptCoordinate($conditions['fingerprint']);
        $smsRequestLog = $this->getSmsRequestLogDao()->create($conditions);
        if (empty($conditions['coordinate'])) {
            $this->createNewException(SmsRequestException::GET_COORDINATE_FAILED);
        }

        if ($this->isIllegalIp($conditions) || $this->isIllegalCoordinate($conditions))
        {
            return true;
        }

        $this->getSmsRequestLogDao()->update($smsRequestLog['id'], ['is_illegal' => '0']);
        return false;
    }

    protected function isIllegalIp($conditions)
    {
        $requestTimesInOneMinute = $this->getSmsRequestLogDao()->count(['ip' => $conditions['ip'], 'startTime' => time() - 60, 'endTime' => time()]);
        // todo 10 读取对应的配置文件
        if ($requestTimesInOneMinute > 10) {
            return true;
        }
    }

    protected function isIllegalCoordinate($conditions)
    {
        $existRequestLogs = $this->getSmsRequestLogDao()->search(['coordinate' => $conditions['coordinate'], 'startTime' => time() - 60 * 10], ['id' => 'DESC'], 0, 10);
        if (count($existRequestLogs) > 3) {
            return true;
        }
    }


    protected function decryptCoordinate($fingerprint)
    {
        global $kernel;
        $csrfToken = $kernel->getContainer()->get('security.csrf.token_manager')->getToken('site');
        return EncryptionToolkit::XXTEADecrypt(base64_decode(mb_substr($fingerprint, 2)), $csrfToken);
    }

    protected function validateConditions($conditions)
    {
        if (empty($conditions)){
            throw $this->createServiceException('sms request 参数不能为空');
        }
        if (empty($conditions['fingerprint'])){
            throw $this->createServiceException('sms request 指纹不能为空');
        }
        if (empty($conditions['ip'])){
            throw $this->createServiceException('sms request IP不能为空');
        }
        if (empty($conditions['mobile'])){
            throw $this->createServiceException('sms request 手机号不能为空');
        }
        if (empty($conditions['user_agent'])){
            throw $this->createServiceException('sms request user agent不能为空');
        }
    }

    /**
     * @return SmsRequestLogDao
     */
    protected function getSmsRequestLogDao()
    {
        return $this->createDao('BehaviorVerification:SmsRequestLogDao');
    }
}