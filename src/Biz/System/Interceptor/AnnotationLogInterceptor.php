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
                    $argumentNum = count($arguments);
                    file_put_contents('/home/kz/test/test', serialize($arguments).PHP_EOL, FILE_APPEND);
                    if (1 == $argumentNum) {
                        $formatReturn = $service->$formatFuncName($arguments[0]);
                    } elseif (2 == $argumentNum) {
                        $formatReturn = $service->$formatFuncName($arguments[0], $arguments[1]);
                    } elseif (3 == $argumentNum) {
                        $formatReturn = $service->$formatFuncName($arguments[0], $arguments[1], $arguments[2]);
                    } else {
                        $formatReturn = array();
                    }
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
                $formats = str_replace("'", '"', $formats);
                $formats = json_decode($formats, true);
                if (isset($formats['after'])) {
                    $format = $formats['after'];
                    $service = $this->biz->service($format['className']);
                    $formatFuncName = $format['funcName'];
                    $arguments = $this->getArrayValue($log['funcParam'], $format['param'], $args);
                    $formatReturn = $service->$formatFuncName($arguments[0]);
                    $context = $formatReturn;
                }
            }
            $message = $log['message'];
            if (!empty($context)) {
                if (is_array($context)) {
                    $this->getLogService()->$level($module, $action, $message, $context);
                }
            }
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
