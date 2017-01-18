<?php

namespace Biz\Task\Strategy;


use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\FreeModeStrategy;
use Biz\Task\Strategy\Impl\LockModeStrategy;
use Biz\Task\Strategy\Impl\PlanStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class StrategyContext
{
    const DEFAULT_STRATEGY = 1;
    const PLAN_STRATEGY = 0;

    private $strategyMap = array();

    private static $_instance = NULL;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new StrategyContext();
        }

        return self::$_instance;
    }

    public function createStrategy($strategyType, $biz)
    {
        if(!empty($this->strategyMap[$strategyType])) {
            return $this->strategyMap[$strategyType];
        }

        switch ($strategyType) {
            case self::PLAN_STRATEGY:
                $this->strategyMap[self::PLAN_STRATEGY] = new PlanStrategy($biz);
                break;
            case self::DEFAULT_STRATEGY :
                $this->strategyMap[self::DEFAULT_STRATEGY] = new DefaultStrategy($biz);
                break;
            default:
                throw new NotFoundException('teach method strategy does not exist');
        }

        return $this->strategyMap[$strategyType];
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->strategy, $name)) {
            throw new \Exception('method not exists.');
        }

        return call_user_func_array(array($this->strategy, $name), $arguments);
    }
}