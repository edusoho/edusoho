<?php

namespace Codeages\Biz\Framework\Service;

use Codeages\Biz\Framework\Context\Biz;

class ServiceProxy
{
    private $biz;
    private $className;
    private $class;
    private $interceptors;
    private $interceptorDatas;

    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->className = $className;
        $this->class = new $className($biz);
        $this->handleInterceptors();
    }

    public function __call($funcName, $arguments)
    {
        foreach ($this->interceptorDatas as $interceptorName => $interceptorData) {
            if (!empty($interceptorData[$funcName])) {
                $this->interceptors[$interceptorName]->exec($funcName, $arguments);
            }
        }

        $result = call_user_func_array(array($this->class, $funcName), $arguments);

        return $result;
    }

    public function handleInterceptors()
    {
        $biz = $this->biz;
        foreach ($biz['interceptors'] as $name => $interceptor) {
            $this->interceptors[$name] = new $interceptor($this->biz, $this->className);
            $this->interceptorDatas[$name] = $this->interceptors[$name]->getInterceptorData();
        }
    }
}
