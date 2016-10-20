<?php

namespace Biz\Activity\Event;

class EventChain
{
    private $events = array();

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function fire($activity, $data)
    {
        foreach ($this->events as $event) {
            $event->trigger($activity, $data);
        }
    }
}
