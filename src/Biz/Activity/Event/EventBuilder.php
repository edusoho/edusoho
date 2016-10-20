<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 12:53
 */

namespace Biz\Activity\Event;


use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\UnexpectedValueException;

class EventBuilder
{
    /**
     * @var Event
     */
    private $event;
    private $eventName;
    private $biz;

    public final static function build(Biz $biz)
    {
        $instance      = new self();
        $instance->biz = $biz;
        return $instance;
    }

    /**
     * @param string $class 事件类名
     * @return EventBuilder
     */
    public final function setEventClass($class)
    {
        // php 5.6之前通过字符串初始化类实例不能传参
        $reflection  = new \ReflectionClass($class);
        $this->event = $reflection->newInstanceArgs(array($this->biz));
        if (!$this->event instanceof Event) {
            throw new UnexpectedValueException('class must be Event Derived Class');
        }
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public final function setName($name)
    {
        $this->eventName = $name;
        return $this;
    }

    /**
     * @return Event
     */
    public final function done()
    {
        if (empty($this->event)) {
            throw new UnexpectedValueException('event not empty');
        }

        $eventName = $this->eventName;
        if (!is_string($eventName) || empty($eventName)) {
            throw new UnexpectedValueException('event name must be a string');
        }

        return $this->event->setName($eventName);
    }
}