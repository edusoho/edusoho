<?php

namespace Biz\System\Interceptor;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Context\AbstractInterceptor;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Targetlog\Annotation\Log;

class AnnotationLogInterceptor extends AbstractInterceptor
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
        $this->interceptorData = $biz['service_log.annotation_reader']->read($className);
    }

    /**
     * @param $args
     */
    public function exec($funcName, $args, $result)
    {
        if (!empty($this->interceptorData[$funcName])) {
            $log = $this->interceptorData[$funcName];
            $currentUser = $this->biz['user'];
            $level = $log['level'];
            $targetType = $log['targetType'];
            $targetId = $log['targetId'];
            $module = $log['module'];
            $action = $log['action'];
            $context = $args;
            if (isset($log['param'])) {
                if ('result' == $log['param']) {
                    $context = $result;
                }
            }
            $message = $log['message'];
            $this->getLogService()->$level($module, $action, $message, $context);
        }
    }

    public function getInterceptorData()
    {
        return $this->interceptorData;
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
