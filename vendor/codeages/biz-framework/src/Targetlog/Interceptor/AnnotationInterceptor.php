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
     * @param $funcName
     * @param $args
     *
     * @return array
     */
    public function beforeExec($funcName, $args)
    {
        $result = array();
        if (!empty($this->interceptorData[$funcName])) {
            $log = $this->interceptorData[$funcName];
            $formats = $log['format'];
            if (!empty($formats)) {
                $formats = str_replace("'", '"', $formats);
                $formats = json_decode($formats, true);
                if (isset($formats['before'])) {
                    $format = $formats['before'];
                    $service = $this->biz->service($format['className']);
                    $formatFuncName = $format['funcName'];
                    $arguments = $this->getArrayValue($log['funcParam'], $format['param'], $args);
                    $formatReturn = $service->$formatFuncName($arguments[0]);
                    $result = $formatReturn;
                }
            }
        }

        return $result;
    }

    /**
     * @param $funcName
     * @param $args
     * @param $result
     * @param array $beforeResult
     */
    public function afterExec($funcName, $args, $result, $beforeResult = array())
    {
        if (!empty($this->interceptorData[$funcName])) {
            $log = $this->interceptorData[$funcName];
            $currentUser = $this->biz['user'];
            $level = $log['level'];
            $targetType = $log['targetType'];
            $targetId = $log['targetId'];
            $module = $log['module'];
            $action = $log['action'];
            $formats = $log['format'];
            $context = empty($beforeResult) ? $result : $beforeResult;
            if (!empty($formats)) {
                $formatReturn = $result;
                $formats = json_decode(str_replace("'", '"', $formats), true);
                if (isset($formats['after'])) {
                    $format = $formats['after'];
                    $service = $this->biz->service($format['className']);
                    $formatFuncName = $format['funcName'];
                    $arguments = $this->getArrayValue($log['funcParam'], $format['param'], $args);
                    $formatReturn = $service->$formatFuncName($arguments[0]);
                }
                $context = $formatReturn;
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
     * @return TargetlogService
     */
    private function getTargetlogService()
    {
        return $this->biz->service('Targetlog:TargetlogService');
    }
}
