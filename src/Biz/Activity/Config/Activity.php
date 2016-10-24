<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:10
 */

namespace Biz\Activity\Config;

use Biz\Activity\Listener\Listener;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\UnexpectedValueException;

abstract class Activity
{
    protected $name = '';

    protected $icon = '';

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

    public final function __construct(Biz $biz)
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
    public abstract function getActionMap();

    /**
     * @param $action
     * @return String
     */
    public final function getAction($action)
    {
        $map = $this->getActionMap();
        return $map[$action];
    }

    /**
     * @return mixed
     */
    protected abstract function getEventMap();

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $eventName
     * @return Listener
     */
    public final function getListener($eventName)
    {
        $map = $this->getEventMap();
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

    protected function getBiz()
    {
        return $this->biz;
    }
}