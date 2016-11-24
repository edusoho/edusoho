<?php

namespace Biz\Activity\Config;

use Biz\Activity\Listener\Listener;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\UnexpectedValueException;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

abstract class Activity
{
    private $biz;

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
    }

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {
    }

    /**
     * @inheritdoc
     */
    public function delete($targetId)
    {
    }

    /**
     * @inheritdoc
     */
    public function get($targetId)
    {
    }

    final public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * Activity 创建、编辑、进行时指定的Controller配置
     *
     * 如：
     * ExampleActivity
     * array(
     *    'create' => 'ExampleBundle:Example:create',
     *    'edit' => 'ExampleBundle:Example:edit',
     *    'create' => 'ExampleBundle:Example:create'
     * )
     *
     * @return array<String, String>
     */
    abstract public function registerActions();

    /**
     * @param  $action
     * @return String
     */
    final public function getAction($action)
    {
        $map = $this->registerActions();
        return $map[$action];
    }

    /**
     * @return mixed
     */
    abstract protected function registerListeners();

    abstract public function getMetas();

    /**
     * @param  string     $eventName
     * @return Listener
     */
    final public function getListener($eventName)
    {
        $map = $this->registerListeners();
        if (empty($map) || !isset($map[$eventName])) {
            return null;
        }
        $reflection = new \ReflectionClass($map[$eventName]);
        $listener   = $reflection->newInstanceArgs(array($this->getBiz()));

        if (!$listener instanceof Listener) {
            throw new UnexpectedValueException("listener class must be Listener Derived Class");
        }

        return $listener;
    }

    protected function createNotFoundService($message = '')
    {
        return new NotFoundException($message);
    }

    protected function createAccessDeniedException($message = '') 
    {
        return new AccessDeniedException($message);
    }

    protected function createInvalidArgumentException($message = '') 
    {
        return new InvalidArgumentException($message);
    }

    /**
     * @return Biz
     */
    protected final function getBiz()
    {
        return $this->biz;
    }
}
