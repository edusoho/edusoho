<?php

namespace Biz\BehaviorVerification\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\BehaviorVerification\Dao\SmsRequestLogDao;
use Biz\BehaviorVerification\AppException;
use Biz\BehaviorVerification\Service\SmsRequestLogService;

class SmsRequestLogServiceImpl extends BaseService implements SmsRequestLogService
{

    public function isRobot($conditions)
    {
        $conditions['module'] = 'send_sms';
        $conditions['is_illegal'] = '1';
        $conditions['coordinate'] = $this->decryptCoordinate($conditions['fingerprint']);
        $smsRequestLogg = $this->getSmsRequestLogDao()->create($conditions);
        if (empty($conditions['coordinate'])) {
            $this->createNewException(AppException::GET_COORDINATE_FAILED);
        }
        // 需要查询，一分钟是不是有十次记录
        $requestTimesInOneMinute = $this->getSmsRequestLogDao()->count(['ip' => $conditions['ip'], 'startTime' => time() - 60, 'endTime' => time()]);
        // todo 10 读取对应的配置文件
        if ($requestTimesInOneMinute > 10) {
            return true;
        }

        // 查询最近10分钟的三条相同
        $existRequestLogCount = $this->getSmsRequestLogDao()->count(['coordinate' => $conditions['coordinate'], 'startTime' => time() - 60 * 10, 'endTime' => time()]);
        if ($existRequestLogCount > 3) {
            return true;
        }
        $this->getSmsRequestLogDao()->update($smsRequestLogg['id'], ['is_illegal' => '0']);
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