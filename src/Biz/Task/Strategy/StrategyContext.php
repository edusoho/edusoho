<?php

namespace Biz\Task\Strategy;

use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\PlanStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class StrategyContext
{
    const DEFAULT_STRATEGY = 1;
    const PLAN_STRATEGY = 0;

    private $strategyMap = array();

    private static $instance = null;

    private $biz = null;

    private function __construct($biz)
    {
        $this->biz = $biz;
    }

    public static function getInstance($biz)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($biz);
        }

        return self::$instance;
    }

    public function createStrategy($strategyType)
    {
        if (!empty($this->strategyMap[$strategyType])) {
            return $this->strategyMap[$strategyType];
        }

        switch ($strategyType) {
            case self::PLAN_STRATEGY:
                $this->strategyMap[self::PLAN_STRATEGY] = new PlanStrategy($this->biz);
                break;
            case self::DEFAULT_STRATEGY:
                $this->strategyMap[self::DEFAULT_STRATEGY] = new DefaultStrategy($this->biz);
                break;
            default:
                throw new NotFoundException('teach method strategy does not exist');
        }

        return $this->strategyMap[$strategyType];
    }

    public function __call($name, $arguments)
    {
        if (!method_exists(static::$instance, $name)) {
            throw new \Exception('method not exists.');
        }

        return call_user_func_array(array(static::$instance, $name), $arguments);
    }
}
