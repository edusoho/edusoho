<?php

namespace Codeages\Biz\Framework\Context;

class Map
{
    private $biz;
    private $className;
    private $class;
    private $interceptors;

    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->className = $className;
        $this->class = new $className($biz);
        $this->registerInterceptors();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->class, $name), $arguments);
    }

    public function registerInterceptors()
    {
        $interceptors = array(
            '\Codeages\Biz\Framework\Targetlog\Interceptor\AnnotationInterceptor',
        );

        foreach ($interceptors as $interceptor) {
            new $interceptor($this->biz, $this->className, $this->interceptors);
        }
    }
}
