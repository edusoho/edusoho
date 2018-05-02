<?php

namespace Codeages\Biz\Framework\Targetlog\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Log
{
    /**
     * @var
     * LOG INFO
     */
    private $message;

    /**
     * @var
     * same to TargetLogService level
     */
    private $level;

    private $action;

    private $targetType;

    private $targetId = 0;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
            }
            $this->$method($value);
        }
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getTargetType()
    {
        return $this->targetType;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
    }

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }
}
