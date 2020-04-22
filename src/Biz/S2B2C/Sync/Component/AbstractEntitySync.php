<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractEntitySync
{
    protected $logger;

    protected $biz;

    protected $processNodes;

    public function __construct(Biz $biz, $processNodes = array())
    {
        $this->biz = $biz;
        $this->processNodes = $processNodes;
        $this->logger = $this->getLogger();
    }

    /**
     * Entity中待sync的字段列表
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
     * 当前sync实体的业务逻辑，注意：
     * 1. 不需要考虑事务
     *
     * @param mixed $source 要sync的对象
     * @param array $config
     *
     * @return mixed
     */
    abstract protected function syncEntity($source, $config = array());

    /**
     * 依次处理要复制的每一个节点
     *
     * @param $originalData
     * @param $config
     */
    protected function processChainsDoSync($originalData, $config)
    {
        $childNodes = $this->processNodes['children'] ?: array();

        foreach ($childNodes as  $currentNode) {
            $nextSyncClass = $currentNode['class'];
            $class = new $nextSyncClass($this->biz);
            $class->sync($originalData, $config);
        }
    }

    /**
     * sync链中的各环节在一个事务中.
     *
     * @param mixed $originalCourse 要sync的对象
     * @param array $course         配置信息
     *
     * @throws \Exception
     *
     * @return mixed
     */
    final public function sync($originalCourse, $course = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            $result = $this->syncEntity($originalCourse, $course);

            $this->biz['db']->commit();

            return $result;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    /**
     * 更新实体的业务逻辑，注意：
     * 1. 不需要考虑事务
     *
     * @param mixed $source 要sync的对象
     * @param array $config
     *
     * @return mixed
     */
    abstract protected function updateEntityToLastedVersion($source, $config = array());

    /**
     * 用于更新现有课程的版本
     *
     * @param mixed $originalCourse 要sync的对象
     * @param array $course         配置信息
     *
     * @throws \Exception
     *
     * @return mixed
     */
    final public function updateToLastedVersion($originalCourse, $course = array())
    {
        try {
            $this->biz['db']->beginTransaction();

            $result = $this->updateEntityToLastedVersion($originalCourse, $course);

            $this->biz['db']->commit();

            return $result;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    /**
     * 依次处理要复制的每一个节点
     *
     * @param $originalData
     * @param $config
     */
    protected function processChainsDoUpdate($originalData, $config)
    {
        $childNodes = $this->processNodes['children'] ?: array();

        foreach ($childNodes as  $currentNode) {
            $nextSyncClass = $currentNode['class'];
            $class = new $nextSyncClass($this->biz);
            $class->updateToLastedVersion($originalData, $config);
        }
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }
}
