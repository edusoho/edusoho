<?php

namespace Biz\BehaviorVerification\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\BehaviorVerification\Dao\SmsRequestLogDao;
use Biz\BehaviorVerification\Service\SmsRequestLogService;

class SmsRequestLogServiceImpl extends BaseService implements SmsRequestLogService
{

    public function isRobot($conditions)
    {
        // 请求记录
        $smsRequestLogg = $this->getSmsRequestLogDao()->create($conditions);
        $conditions['coordinate'] = $this->decryptCoordinate($conditions['fingerprint']);
        if (isEmpty($conditions['coordinate'])) {
            return true;
        }
        // 需要查询，一分钟是不是有十次记录
        $requestTimesInOneMinute = $this->getSmsRequestLogDao()->count(['ip' => $conditions['ip'], 'startTime' => time() - 60, 'endTime' => time()]);
        // todo 10 读取对应的配置文件
        if ($requestTimesInOneMinute > 10) {
            return true;
        }
        // 查询最近10分钟的三条相同
        $existRequestLogs = $this->getSmsRequestLogDao()->search(['coordinate' => $conditions['coordinate'], 'startTime' => time() - 60 * 10, 'endTime' => time()], ['desc' => 'id'], 0, PHP_INT_MAX);
        $existRequestLogsCount = array_count_values(array_column($existRequestLogs, 'coordinate'));
        if (!empty($existRequestLogsCount) && $existRequestLogsCount[$conditions['coordinate']] > 3) {
            return true;
        }
        $this->getSmsRequestLogDao()->update($smsRequestLogg['id'], ['coordinate' => $conditions['coordinate']]);
        return false;
    }


    protected function decryptCoordinate($fingerprint)
    {
        global $kernel;
        $csrfToken = $kernel->getContainer()->get('security.csrf.token_manager')->getToken('site');
        return EncryptionToolkit::XXTEADecrypt(base64_decode(mb_substr($fingerprint, 2)), $csrfToken);
    }

    /**
     * @return SmsRequestLogDao
     */
    protected function getSmsRequestLogDao()
    {
        return $this->createDao('BehaviorVerification:SmsRequestLogDao');
    }
}