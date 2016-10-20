<?php

namespace Biz\Activity\Event;

class EventChain
{
    /**
     * @var Event[]
     */
    private $events = array();

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function fire()
    {
        foreach ($this->events as $event) {
            $event->trigger();
        }
    }
}
