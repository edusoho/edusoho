<?php

namespace Codeages\Biz\Framework\Context;

class Map
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
        $this->registerInterceptors();
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

        return call_user_func_array(array($this->class, $name), $arguments);
    }

    public function registerInterceptors()
    {
        $interceptors = array(
            'target_log' => '\Codeages\Biz\Framework\Targetlog\Interceptor\AnnotationInterceptor',
        );
        foreach ($interceptors as $name => $interceptor) {
            $this->interceptors[$name] = new $interceptor($this->biz, $this->className, $this->interceptorData);
        }
    }
}
