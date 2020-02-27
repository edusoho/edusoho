<?php

namespace Biz\Live\LiveStatisticsProcessor;

use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Service\Common\ServiceKernel;

abstract class AbstractLiveStatisticsProcessor
{
    private $biz;

    const RESPONSE_CODE_SUCCESS = 0;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function handlerResult($result);

    protected function getLogService()
    {
        return ServiceKernel::instance()->createService('System:LogService');
    }

    protected function getUserIdByNickName($nickname)
    {
        $userId = trim(strrchr($nickname, '_'), '_');
        //考虑老数据的情况，不建议在循环中getUserByNickname
        if (!is_numeric($userId) || empty($userId)) {
            return 0;
        }

        return $userId;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
