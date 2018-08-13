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
            $formats = $log['format'];
            $param = $log['param'];
            $context = $result;
            if (!empty($formats)) {
                $formatReturn = $result;
                $formats = str_replace("'", '"', $formats);
                $formats = json_decode($formats, true);
                foreach ($formats as $format) {
                    $service = $this->biz->service($format['className']);
                    $funcName = $format['funcName'];
                    $arguments = $this->getArrayValue($log['param'], $format['param'], $args);
                    $formatReturn = $service->$funcName($arguments[0]);
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

    private function getArrayValue($array, $index, $args)
    {
        $returnArray = array();
        foreach ($index as $value) {
            foreach ($array as $k => $v) {
                if ($v == $value) {
                    $returnArray[] = $args[$k];
                }
            }
        }

        return $returnArray;
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
