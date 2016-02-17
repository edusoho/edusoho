<?php

namespace Topxia\Service\Common\Annotations;

class Annotation
{
    protected $data;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters.
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        }
        throw new \RuntimeException("{$name} is not exist in ".get_class($this));
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new \RuntimeException("{$name} is not exist in ".get_class($this));
    }

    public function getAspect()
    {
        if (array_key_exists('aspect', $this->data)
            && in_array($this->data['aspect'], array('before', 'after', 'around'))) {
            return $this->data['aspect'];
        }

        return 'before';
    }
}
