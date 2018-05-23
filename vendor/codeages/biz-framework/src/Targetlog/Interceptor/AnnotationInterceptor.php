<?php

namespace Codeages\Biz\Framework\Targetlog\Interceptor;

use Codeages\Biz\Framework\Context\AbstractInterceptor;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Targetlog\Annotation\Log;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationInterceptor extends AbstractInterceptor
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * AnnotationInterceptor constructor.
     *
     * @param Biz $biz
     * @param $className
     * @param $interceptorData
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(Biz $biz, $className, &$interceptorData)
    {
        $this->biz = $biz;
        $this->interceptorData = $biz['service.annotation_reader']->read($className);
        $interceptorData = $this->interceptorData;
    }

    /**
     * @param $annotation Log
     * @param $args
     */
    public function exec($annotation, $args)
    {
        if (!empty($annotation)) {
            $currentUser = $this->biz['user'];
            $level = $annotation->getLevel();
            $targetType = $annotation->getTargetType();
            $targetId = $annotation->getTargetId();
            $context['@action'] = $annotation->getAction();
            $context['@args'] = $args;
            $context['@user_id'] = empty($currentUser['id']) ? 0 : $currentUser['id'];
            $context['@ip'] = empty($currentUser['currentIp']) ? '' : $currentUser['currentIp'];
            $message = $annotation->getMessage();
            $this->getTargetlogService()->log($level, $targetType, $targetId, $message, $context);
        }
    }

    /**
     * @return TargetlogService
     */
    private function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}
