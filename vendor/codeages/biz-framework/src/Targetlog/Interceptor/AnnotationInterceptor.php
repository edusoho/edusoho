<?php

namespace Codeages\Biz\Framework\Targetlog\Interceptor;

use Codeages\Biz\Framework\Context\Biz;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationInterceptor
{
    /**
     * AnnotationInterceptor constructor.
     *
     * @param Biz $biz
     * @param $className
     * @param $interceptor
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(Biz $biz, $className, &$interceptor)
    {
        $annotationReader = new AnnotationReader();
        $reflectClass = new \ReflectionClass($className);
        $interfaces = $reflectClass->getInterfaces();
        foreach ($interfaces as $interfaceName => $interfaceObj) {
            $reflectInterface = new \ReflectionClass($interfaceName);
            $methods = $reflectInterface->getMethods();
            foreach ($methods as $method) {
                $annotation = $annotationReader->getMethodAnnotation($method, 'Codeages\Biz\Framework\TargetLog\Annotation\Log');
                $interceptor[$method->getName()]['annotation'] = $annotation;
            }
        }
    }
}
