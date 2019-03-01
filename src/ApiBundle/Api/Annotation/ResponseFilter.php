<?php

namespace ApiBundle\Api\Annotation;

use Biz\Common\CommonException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ResponseFilter
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $mode;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw CommonException::NOTFOUND_METHOD();
            }
            $this->$method($value);
        }
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}
