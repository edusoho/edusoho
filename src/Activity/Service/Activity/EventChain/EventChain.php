<?php

namespace Activity\Service\Activity\EventChain;

class EventChain
{
    private $events = array();

    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    public function trigger($activity, $data)
    {
        foreach ($this->events as $event) {
            $event->trigger($activity, $data);
        }
    }
}
