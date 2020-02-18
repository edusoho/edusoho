<?php

namespace Biz\System\Interceptor;

use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Context\AbstractInterceptor;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Targetlog\Annotation\Log;
use Topxia\Service\Common\ServiceKernel;
use Biz\System\Util\LogDataUtils;

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
            $formatFuncName = $this->getFormatFuncName($funcName, $log);
            if (false !== $formatFuncName) {
                $formatReturn = array();
                if (!empty($log['funcParam'])) {
                    $formatParam = $this->getFormatParam($args, $log);
                    $formatReturn = $this->getFormatReturn($service, $formatFuncName, $formatParam, $log);
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
            $action = $this->getActionValue($args, $log);
            $context = empty($beforeResult) ? $result : $beforeResult;

            if ('update' == $this->getFuncType($action)) {
                if (empty($beforeResult)) {
                    $beforeResult = array();
                }
                $service = $this->biz->service($log['service']);
                $formatParam = $this->getFormatParam($args, $log);
                $formatFuncName = $this->getFormatFuncName($funcName, $log);
                $formatReturn = $this->getFormatReturn($service, $formatFuncName, $formatParam, $log);
                $courseChangeFields = LogDataUtils::serializeChanges($beforeResult, $formatReturn);
                $context = $courseChangeFields;
            }
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

    private function getFormatFuncName($funcName, $log)
    {
        $formatFuncName = false;
        if (!empty($log['funcName'])) {
            $formatFuncName = $log['funcName'];
        } else {
            $keyArray = array('delete', 'update');
            foreach ($keyArray as $key) {
                if (false !== strpos($funcName, $key)) {
                    $formatFuncName = str_replace($key, 'get', $funcName);
                    break;
                }
            }
        }

        return $formatFuncName;
    }

    private function getFuncType($action)
    {
        if (false !== strpos($action, 'password')) {
            return 'password';
        }
        $config = array(
            'add' => 'create',
            'generate' => 'create',
            'create' => 'create',
            'delete' => 'delete',
        );
        foreach ($config as $key => $value) {
            if (false !== strpos($action, $key)) {
                return $value;
            }
        }

        return 'update';
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

    private function getActionValue($args, $log)
    {
        $action = $log['action'];
        if (!empty($log['postfix'])) {
            $postfix = $this->getPostfixValue($args, $log);
            if (!empty($postfix)) {
                $action .= '.'.$postfix;
            }
        }

        return $action;
    }

    private function getPostfixValue($args, $log)
    {
        $postfixValue = '';
        if (!empty($log['postfix'])) {
            $postfixs = explode(',', $log['postfix']);
            $postfix = $this->getFuncNeedParams($log['funcParam'], $postfixs, $args);
            $postfixValue = implode('.', $postfix);
        }

        return $postfixValue;
    }

    private function getFormatReturn($service, $formatFuncName, $formatParam, $log)
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

            $this->getLogService()->warning($module, $action, '调用前置方法错误', $exception->getMessage());
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
