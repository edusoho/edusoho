<?php

namespace Biz\SmsDefence\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\SmsDefence\Dao\SmsBlackListDao;
use Biz\SmsDefence\Dao\SmsRequestLogDao;
use Biz\SmsDefence\Service\SmsDefenceService;

class SmsDefenceServiceImpl extends BaseService implements SmsDefenceService
{
    public function validate($fields)
    {
        if ($this->isInBlackIpList($fields['ip'])) {
            return true;
        }

        if ($this->isIllegalIp($fields['ip'])) {
            $fields['disableType'] = 'ip';
            $fields['isIllegal'] = 1;
            $this->createSmsRequestLog($fields);
            $this->addBlackIpList($fields['ip']);

            return true;
        }

        if ($this->isIllegalCoordinate($fields['fingerprint'])) {
            $fields['disableType'] = 'coordinate';
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
        $smsBlackIp = $this->getSmsBlackListDao()->getByIp($ip);
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
        $smsBlackIp = $this->getSmsBlackListDao()->getByIp($ip);
        if (empty($smsBlackIp)) {
            $this->getSmsBlackListDao()->create(['ip' => $ip, 'expire_time' => time() + 7 * 24 * 3600]);
            return;
        }

        $this->getSmsBlackListDao()->update($smsBlackIp['id'], ['expire_time' => time() + 7 * 24 * 3600]);
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
            'disableType' => $fields['disableType'] ?? 'none',
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

    public function searchSmsRequestLog($conditions, $sort, $start, $limit)
    {
        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = array('id' => 'DESC');
                    break;
                case 'createdByAsc':
                    $sort = array('id' => 'ASC');
                    break;
                default:
                    $this->createNewException(CommonException::ERROR_PARAMETER);
                    break;
            }
        }
        return $this->getSmsRequestLogDao()->search($conditions, $sort, $start, $limit);
    }

    public function searchSmsBlackIpList($conditions, $sort, $start, $limit)
    {
        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = array('id' => 'DESC');
                    break;
                case 'createdByAsc':
                    $sort = array('id' => 'ASC');
                    break;
                default:
                    $this->createNewException(CommonException::ERROR_PARAMETER);
                    break;
            }
        }
        return $this->getSmsBlackListDao()->search($conditions, $sort, $start, $limit);
    }

    public function unLockBlackIp($id)
    {
        if (empty($id)) {
            return array();
        }
        $this->getSmsBlackListDao()->update($id, ['expire_time' => time() - 3600]);

        return true;
    }

    public function countSmsRequestLog($conditions)
    {
        return $this->getSmsRequestLogDao()->count($conditions);
    }

    public function countSmsBlackIpList($conditions)
    {
        return $this->getSmsBlackListDao()->count($conditions);
    }

    public function getSmsRequestLog($id)
    {
        if (empty($id)) {
            return array();
        }
        return $this->getSmsRequestLogDao()->get($id);
    }

    /**
     * @return SmsRequestLogDao
     */
    protected function getSmsRequestLogDao()
    {
        return $this->createDao('SmsDefence:SmsRequestLogDao');
    }

    /**
     * @return SmsBlackListDao
     */
    protected function getSmsBlackListDao()
    {
        return $this->createDao('SmsDefence:SmsBlackListDao');
    }
}
