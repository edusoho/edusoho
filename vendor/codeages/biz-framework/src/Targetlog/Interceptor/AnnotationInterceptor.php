<?php

namespace Codeages\Biz\Framework\Targetlog\Interceptor;

use Codeages\Biz\Framework\Context\AbstractInterceptor;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Targetlog\Annotation\Log;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class AnnotationInterceptor extends AbstractInterceptor
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var log
     */
    protected $log;

    /**
     * AnnotationInterceptor constructor.
     *
     * @param Biz $biz
     * @param $className
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function __construct(Biz $biz, $className)
    {
        $this->biz = $biz;
        $this->interceptorData = $biz['service_targetlog.annotation_reader']->read($className);
    }

    /**
     * @param $args
     */
    public function exec($funcName, $args, $result)
    {
        if (!empty($this->interceptorData[$funcName])) {
            $log = $this->interceptorData[$funcName];
            $currentUser = $this->biz['user'];
            $levelId = $log['levelId'];
            $targetType = $log['targetType'];
            $targetId = $log['targetId'];
            $context = $args;
            if (isset($log['param'])) {
                if ('result' == $log['param']) {
                    $context = $result;
                }
            }
            $message = $log['message'];
            $this->getTargetlogService()->log($levelId, $targetType, $targetId, $message, $context);
        }
    }

    public function getInterceptorData()
    {
        return $this->interceptorData;
    }

    /**
     * @return TargetlogService
     */
    private function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}
