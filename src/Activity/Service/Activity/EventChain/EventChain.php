<?php

namespace Activity\Service\Activity\EventChain;

class EventChain
{
    private $events = array();

    public function add(Event $event)
    {
        $events[] = $event;
    }

    public function trigger($data)
    {
        foreach ($events as $event) {
            $event->trigger($data);
        }
    }
}
