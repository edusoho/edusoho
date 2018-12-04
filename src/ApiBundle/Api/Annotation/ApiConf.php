<?php

namespace ApiBundle\Api\Annotation;

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
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
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
