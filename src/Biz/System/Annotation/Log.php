<?php

namespace Biz\System\Annotation;

use Biz\Common\CommonException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Log
{
    const DEBUG = 'debug'; //1
    const INFO = 'info'; //2
    const NOTICE = 'notice'; //3
    const WARNING = 'warning'; //4
    const ERROR = 'error'; //5
    const CRITICAL = 'critical'; //6
    const ALERT = 'alter'; //7
    const EMERGENCY = 'emergency'; //8

    const LEVEL_DEBUG = 1;
    const LEVEL_INFO = 2;
    const LEVEL_NOTICE = 3;
    const LEVEL_WARNING = 4;
    const LEVEL_ERROR = 5;
    const LEVEL_CRITICAL = 6;
    const LEVEL_ALERT = 7;
    const LEVEL_EMERGENCY = 8;

    /**
     * @var
     * same to TargetLogService level
     */
    private $level;

    private $action;

    private $module;

    private $param;

    private $funcName;

    private $serviceName = '';

    private $postfix = '';

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw CommonException::NOTFOUND_METHOD();
            }
            $this->$method($value);
        }
    }

    public function getLevelId()
    {
        $levelId = self::LEVEL_DEBUG;
        switch ($this->level) {
            case self::DEBUG:
                $levelId = self::LEVEL_DEBUG;
                break;
            case self::INFO:
                $levelId = self::LEVEL_INFO;
                break;
            case self::NOTICE:
                $levelId = self::LEVEL_NOTICE;
                break;
            case self::WARNING:
                $levelId = self::LEVEL_WARNING;
                break;
            case self::ERROR:
                $levelId = self::LEVEL_ERROR;
                break;
            case self::CRITICAL:
                $levelId = self::LEVEL_CRITICAL;
                break;
            case self::ALERT:
                $levelId = self::LEVEL_ALERT;
                break;
            case self::EMERGENCY:
                $levelId = self::LEVEL_EMERGENCY;
                break;
            default:
                $levelId = self::LEVEL_DEBUG;
        }

        return $levelId;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function getFuncName()
    {
        return $this->funcName;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function getPostfix()
    {
        return $this->postfix;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setParam($param)
    {
        $this->param = $param;
    }

    public function setFuncName($funcName)
    {
        $this->funcName = $funcName;
    }

    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;
    }
}
