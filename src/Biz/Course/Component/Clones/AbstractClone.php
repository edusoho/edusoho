<?php

namespace Biz\Course\Component\Clones;

use Codeages\Biz\Framework\Context\Biz;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use AppBundle\Common\ArrayToolkit;

abstract class AbstractClone
{
    protected $logger;

    protected $biz;

    protected $processNodes;

    protected $auto;

    public function __construct(Biz $biz, $processNodes = array(), $auto = false)
    {
        $this->biz = $biz;
        $this->processNodes = $processNodes;
        $this->auto = $auto;
    }

    abstract protected function cloneEntity($source, $options);

    final public function clones($source, $options = array())
    {
        $result = $this->cloneEntity($source, $options);
        if ($this->auto) {
            $this->processChainsDoClone($source, $options);
        }

        return $result;
    }

    protected function processChainsDoClone($source, $options)
    {
        foreach ($this->processNodes as  $currentNode) {
            $class = new $currentNode['class']($this->biz);
            $class->clones($source, $options);
        }
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

    protected function getLogger($name)
    {
        if ($this->logger) {
            return $this->logger;
        }

        $this->logger = new Logger($name);
        $this->logger->pushHandler(new StreamHandler($this->biz['log_directory'].'/service.log', Logger::DEBUG));

        return $this->logger;
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
}
