<?php

namespace Biz\Course\Copy;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractEntityCopy
{
    private $logger;

    protected $biz;

    protected $children;

    protected $node;

    public function __construct(Biz $biz, $node)
    {
        $this->biz = $biz;
        $this->node = $node;
        $chain = call_user_func($this->biz['course_copy.chains'], $node);
        if (!empty($chain) && !empty($chain['children'])) {
            $this->children = $chain['children'];
        } else {
            $this->children = array();
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
    protected function copyFields($source)
    {
        $fields = $this->getFields();

        $new = array();
        foreach ($fields as $field) {
            if (!empty($source[$field]) || $source[$field] == 0) {
                $new[$field] = $source[$field];
            }
        }

        return $new;
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

    protected function childrenCopy($source, $config = array())
    {
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $cls = new $child['clz']($this->biz, $this->node);
                $cls->copy($source, $config);
            }
        }
    }

    /**
     * copy链中的各环节在一个事务中.
     *
     * @param mixed $source 要copy的对象
     * @param mixed $parent copy链中已创建的直接父类对象
     * @param array $config 配置信息
     *
     * @throws \Exception
     *
     * @return mixed
     */
    final public function copy($source, $config = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            $result = $this->copyEntity($source, $config);

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
