<?php

namespace ApiBundle\Api\Annotation;

use Biz\Common\CommonException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class ApiConf
{
    /**
     * @var bool
     */
    private $isRequiredAuth;

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

    public function getIsRequiredAuth()
    {
        return $this->isRequiredAuth;
    }

    public function setIsRequiredAuth($isRequiredAuth)
    {
        $this->isRequiredAuth = $isRequiredAuth;
    }
}
