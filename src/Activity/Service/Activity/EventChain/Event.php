<?php

namespace Activity\Service\Activity\EventChain;

interface Event
{
    public function trigger($activity, $data);
}
