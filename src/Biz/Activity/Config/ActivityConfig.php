<?php
/**
 * User: retamia
 * Date: 2016/10/20
 * Time: 17:04
 */

namespace Biz\Activity\Config;


use Biz\Activity\Event\Event;
use Biz\Activity\Event\EventBuilder;
use Codeages\Biz\Framework\Context\Biz;
use Topxia\Common\Exception\UnexpectedValueException;

abstract class ActivityConfig
{
    /**
     * @var Biz
     */
    private $biz;


    final public function __construct(Biz $biz)
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
     * @param string $eventName
     * @return Event
     */
    public final function getEvent($eventName)
    {
        $map = $this->getEventMap();
        if(empty($map) || !isset($map[$eventName])){
            return null;
        }

        return EventBuilder::build($this->biz)
            ->setName($eventName)
            ->setEventClass($map[$eventName])
            ->done();
    }

    protected abstract function getEventMap();

    final protected function getBiz()
    {
        return $this->biz;
    }
}