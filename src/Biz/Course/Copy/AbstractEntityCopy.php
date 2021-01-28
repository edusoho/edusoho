<?php

namespace Biz\Course\Copy;

use AppBundle\Common\ArrayToolkit;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractEntityCopy
{
    private $logger;

    protected $biz;

    protected $processNodes;

    public function __construct(Biz $biz, $processNodes = array())
    {
        $this->biz = $biz;
        $this->processNodes = $processNodes;
    }

    /**
     * Entity中待copy的字段列表
     *
     * @return array
     */
    abstract protected function getFields();

    /**
     * 根据getFields配置原封不动的复制Entity信息到新Entity
     *
     * @param $source
     *
     * @return array
     */
    protected function filterFields($source)
    {
        $fields = $this->getFields();

        return ArrayToolkit::parts($source, $fields);
    }

    /**
     * 当前copy实体的业务逻辑，注意：
     * 1. 不需要考虑事务
     *
     * @param mixed $source 要copy的对象
     * @param array $config
     *
     * @return mixed
     */
    abstract protected function copyEntity($source, $config = array());

    /**
     * 依次处理要复制的每一个节点
     *
     * @param $originalCourse
     * @param $course
     */
    protected function processChainsDoCopy($originalCourse, $course)
    {
        $childNodes = $this->processNodes['children'] ?: array();

        foreach ($childNodes as  $currentNode) {
            $nextCopyClass = $currentNode['class'];
            $class = new $nextCopyClass($this->biz);
            $class->copy($originalCourse, $course);
        }
    }

    /**
     * copy链中的各环节在一个事务中.
     *
     * @param mixed $originalCourse 要copy的对象
     * @param array $course         配置信息
     *
     * @throws \Exception
     *
     * @return mixed
     */
    final public function copy($originalCourse, $course = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            $result = $this->copyEntity($originalCourse, $course);

            $this->biz['db']->commit();

            return $result;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    protected function addError($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->error($message);
    }

    protected function addDebug($logName, $message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $this->getLogger($logName)->debug($message);
    }

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/service.log', Logger::DEBUG));

        return $this->logger;
    }
}
