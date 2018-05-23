<?php

namespace Codeages\Biz\Framework\Service;
use Codeages\Biz\Framework\Context\Biz;

class ServiceProxy
{
    private $biz;
    private $className;
    private $class;
    private $interceptors;
    private $interceptorData;

    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->className = $className;
        $this->class = new $className($biz);
        $this->handleInterceptors();
    }

    public function __call($name, $arguments)
    {
        if (isset($this->interceptorData[$name])) {
            $funcInterceptors = $this->interceptorData[$name];
            if (!empty($funcInterceptors) && is_array($funcInterceptors)) {
                foreach ($funcInterceptors as $interceptorName => $value) {
                    $this->interceptors[$interceptorName]->exec($value, $arguments);
                }
            }
        }

        $result = call_user_func_array(array($this->class, $name), $arguments);

        return $result;
    }

    public function handleInterceptors()
    {  
        $biz = $this->biz;
        foreach ($biz['interceptors'] as $name => $interceptor) {
            $this->interceptors[$name] = new $interceptor($this->biz, $this->className, $this->interceptorData);
        }
    }
}
