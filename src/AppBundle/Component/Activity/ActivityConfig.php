<?php

namespace AppBundle\Component\Activity;

use AppBundle\Common\Exception\UnexpectedValueException;

class ActivityConfig implements \ArrayAccess
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = empty($config) ? array() : $config;
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->config)) {
            return $this->config[$name];
        }
        throw new UnexpectedValueException("{$name} is not exist.");
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    public function __unset($name)
    {
        unset($this->config[$name]);
    }
}
