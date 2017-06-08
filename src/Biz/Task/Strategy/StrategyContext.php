<?php

namespace Biz\Task\Strategy;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class StrategyContext
{
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
        throw new NotFoundException("course strategy {$strategyType} does not exist");
    }

    public function __call($name, $arguments)
    {
        if (!method_exists(static::$instance, $name)) {
            throw new \Exception('method not exists.');
        }

        return call_user_func_array(array(static::$instance, $name), $arguments);
    }
}
