<?php

namespace Biz\System\Interceptor;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Context\AbstractInterceptor;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Targetlog\Annotation\Log;
use Topxia\Service\Common\ServiceKernel;

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
            $service = $this->biz->service($log['service']);
            $formatFuncName = $this->getGetFuncName($funcName);
            if (false !== $formatFuncName) {
                $formatReturn = array();
                if (!empty($log['funcParam'])) {
                    $formatParam = $this->getFormatParam($args, $log);
                    $formatFuncName = $this->getFormatFuncName($formatFuncName, $log);
                    $formatReturn = $this->getFormatReturn($service, $formatFuncName, $formatParam);
                }
                $result = $formatReturn;
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
            $module = $log['module'];
            $action = $log['action'];
            $context = empty($beforeResult) ? $result : $beforeResult;
//            if (!empty($formats)) {
//                $formats = str_replace("'", '"', $formats);
//                $formats = json_decode($formats, true);
//                if (isset($formats['after'])) {
//                    $format = $formats['after'];
//                    $service = $this->biz->service($format['className']);
//                    $formatFuncName = $format['funcName'];
//                    $arguments = $this->getArrayValue($log['funcParam'], $format['param'], $args);
//                    $formatReturn = $service->$formatFuncName($arguments[0]);
//                    $context = $formatReturn;
//                }
//            }
            $message = ServiceKernel::instance()->trans('log.action.'.$module.'.'.$action, array(), null, null);
            if (!empty($context)) {
                if (is_array($context)) {
                    $this->getLogService()->info($module, $action, $message, $context);
                }
            }
        }
    }

    public function getInterceptorData()
    {
        return $this->interceptorData;
    }

    private function getGetFuncName($funcName)
    {
        $keyArray = array('delete', 'update');
        foreach ($keyArray as $key) {
            if (false !== strpos($funcName, $key)) {
                return str_replace($key, 'get', $funcName);
            }
        }

        return false;
    }

    private function getFuncNeedParams($funcParams, $indexs, $args)
    {
        $funcNeedParams = array();
        foreach ($indexs as $index) {
            foreach ($funcParams as $key => $funcParam) {
                if ($funcParam == $index) {
                    $funcNeedParams[] = $args[$key];
                }
            }
        }

        return $funcNeedParams;
    }

    private function getFormatParam($args, $log)
    {
        $formatParam = $args;
        if (!empty($log['param'])) {
            $params = explode(',', $log['param']);
            $formatParam = $this->getFuncNeedParams($log['funcParam'], $params, $args);
        }

        return $formatParam;
    }

    private function getFormatFuncName($formatFuncName, $log)
    {
        if (!empty($log['funcName'])) {
            $formatFuncName = $log['funcName'];
        }

        return $formatFuncName;
    }

    private function getFormatReturn($service, $formatFuncName, $formatParam)
    {
        $formatReturn = array();
        try {
            $paramCount = count($formatParam);
            if (1 == $paramCount) {
                $formatReturn = $service->$formatFuncName($formatParam[0]);
            } elseif (2 == $paramCount) {
                $formatReturn = $service->$formatFuncName($formatParam[0], $formatParam[1]);
            }
        } catch (\Exception $exception) {
            $module = $log['module'];
            $action = $log['action'];

            $e = $this->exceptionToArray($exception);

            $this->getLogService()->warning($module, $action, '调用前置方法错误', $e);
        }

        return $formatReturn;
    }

    private function exceptionToArray(\Exception $exception)
    {
        $exceptionArray = array(
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'message' => $exception->getMessage(),
            'previous' => $exception->getPrevious(),
            'trace' => $exception->getTrace(),
            'traceAsString' => $exception->getTraceAsString(),
        );

        return $exceptionArray;
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
