<?php

namespace ApiBundle\Api\Annotation;

use Biz\Common\CommonException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class AuthClass
{
    /**
     * @var bool
     */
    private $className;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.$key;
            if (!method_exists($this, $method)) {
                throw CommonException::NOTFOUND_METHOD();
            }
            $this->$method($value);
        }
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }
}
