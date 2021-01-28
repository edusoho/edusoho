<?php

namespace Codeages\Biz\Framework\Scheduler;

use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractJob implements Job, \ArrayAccess
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const RETRY = 'retry';

    private $params = array();

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($params = array(), $biz = null)
    {
        $this->params = $params;
        $this->biz = $biz;
    }

    abstract public function execute();

    public function __get($name)
    {
        return empty($this->params[$name]) ? '' : $this->params[$name];
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }
}
