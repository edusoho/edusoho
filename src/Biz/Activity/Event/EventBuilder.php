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
    private $biz;

    public final static function build(Biz $biz)
    {
        $instance = new self();
        $instance->biz = $biz;
        return $instance;
    }

    /**
     * @param string $class 事件类名
     * @return EventBuilder
     */
    public final function setEventClass($class)
    {
        $this->event = new $class($this->biz);
        if(!$this->event instanceof Event){
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
        $this->event->setName($name);
        return $this;
    }

    /**
     * @return Event
     */
    public final function done()
    {
        if(empty($this->event)){
            throw new UnexpectedValueException('event not empty');
        }

        $eventName = $this->event->getName();
        if(!is_string($eventName) || empty($eventName)){
            throw new UnexpectedValueException('event name must be a string');
        }

        return $this->event;
    }
}