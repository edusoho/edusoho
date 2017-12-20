<?php

namespace AppBundle\Component\Activity;

class ActivityConfig implements \ArrayAccess
{
    private $raw;

    public function __construct($jsonPath)
    {
        if (!file_exists($jsonPath)) {
            throw new \RuntimeException('The activity.json not found');
        }
        $this->raw = json_decode(file_get_contents($jsonPath), true);

        if (!$this->raw) {
            throw new \RuntimeException('The activity.json json format error');
        }

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
        $this->raw[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->raw)) {
            return $this->raw[$name];
        }
        throw new \RuntimeException("{$name} is not exist.");
    }

    public function __isset($name)
    {
        return isset($this->raw[$name]);
    }

    public function __unset($name)
    {
        unset($this->raw[$name]);
    }

}