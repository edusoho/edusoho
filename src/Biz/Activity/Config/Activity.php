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
    {}

    /**
     * @inheritdoc
     */
    public function update($targetId, $fields)
    {}

    /**
     * @inheritdoc
     */
    public function delete($targetId)
    {}

    /**
     * @inheritdoc
     */
    public function get($targetId)
    {}

    public final function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @return ActivityRenderer
     */
    public final function getRenderer()
    {
        $class = $this->getRendererClass();
        // php 5.6之前通过字符串初始化类实例不能传参
        $reflection = new \ReflectionClass($class);
        $renderer = $reflection->newInstanceArgs(array($this->getBiz()));
        if(!$renderer instanceof ActivityRenderer){
            throw new UnexpectedValueException("renderer class must be ActivityRenderer Derived Class");
        }

        return $renderer;
    }

    /**
     * @return string
     */
    protected abstract function getRendererClass();

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
        if(empty($map) || !isset($map[$eventName])){
            return null;
        }
        $reflection = new \ReflectionClass($map[$eventName]);
        $listener = $reflection->newInstanceArgs(array($this->getBiz()));

        if(!$listener instanceof Listener){
            throw new UnexpectedValueException("listener class must be Listener Derived Class");
        }

        return $listener;
    }

    protected function getBiz()
    {
        return $this->biz;
    }
}