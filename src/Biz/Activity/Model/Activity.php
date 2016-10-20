<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 11:10
 */

namespace Biz\Activity\Model;


use Codeages\Biz\Framework\Context\Biz;
use Biz\Activity\Event\Event;
use Biz\Activity\Event\EventBuilder;
use Topxia\Common\Exception\UnexpectedValueException;

abstract class Activity
{
    public $name;

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
    public abstract function getRendererClass();

    /**
     * @param string $eventName
     * @return Event
     */
    public final function getEvent($eventName)
    {
        $map = $this->getEventMap();
        if(empty($map) && !isset($map[$eventName])){
            return null;
        }

        return EventBuilder::build($this->biz)->setName($eventName)->setEventClass($map[$eventName]);
    }

    /**
     * @inheritdoc
     */
    public function getEventMap()
    {
    }

    public function getBiz()
    {
        return $this->biz;
    }
}