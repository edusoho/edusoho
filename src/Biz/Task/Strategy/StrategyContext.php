<?php

namespace Biz\Task\Strategy;

use Biz\Common\CommonException;

class StrategyContext
{
    private $biz = null;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    protected function getStrategyType($courseType)
    {
        return 'course.'.$courseType.'_strategy';
    }

    public function createStrategy($courseType)
    {
        $strategyType = $this->getStrategyType($courseType);
        if (isset($this->biz[$strategyType])) {
            return $this->biz[$strategyType];
        }
        throw CommonException::NOTFOUND_SERVICE_PROVIDER();
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this, $name)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        return call_user_func_array(array($this, $name), $arguments);
    }
}
