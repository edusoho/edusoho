<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Biz\Live\LiveStatisticsException;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

abstract class AbstractLiveStatisticsProcessor
{
    protected $biz;

    const RESPONSE_CODE_SUCCESS = 0;

    const RESPONSE_CODE_NOT_FOUND = 4001;

    const RESPONSE_CODE_NOT_SUPPORT = 4002;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function handlerResult($result);

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    protected function splitUserIdFromNickName($nickname)
    {
        $userId = trim(strrchr($nickname, '_'), '_');
        if (!is_numeric($userId) || empty($userId)) {
            return 0;
        }

        return $userId;
    }

    protected function checkResult($result)
    {
        if (!isset($result['code'])) {
            $this->getLogService()->info('course', 'live', 'check code error: '.json_encode($result));
            throw new ServiceException('code is not found');
        }

        if (in_array($result['code'], array(self::RESPONSE_CODE_NOT_FOUND, self::RESPONSE_CODE_NOT_SUPPORT))) {
            throw new LiveStatisticsException($result['code']);
        } elseif ($result['code'] != self::RESPONSE_CODE_SUCCESS) {
            throw new ServiceException('code is not valid');
        }

        if (!isset($result['data'])) {
            $this->getLogService()->info('course', 'live', 'check data error: '.json_encode($result));
            throw new ServiceException('data is not found');
        }

        return true;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
