<?php

namespace Biz\Live\LiveStatisticsProcessor;

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
}