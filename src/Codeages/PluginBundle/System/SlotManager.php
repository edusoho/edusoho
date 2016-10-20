<?php
namespace Codeages\PluginBundle\System;

use Symfony\Component\EventDispatcher\EventDispatcher;

class SlotManager
{
    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function fire($name)
    {
        $event = $event = new SlotEvent($args);
        $this->dispatcher->dispatch($name, $event);
    }

    public function on()
    {

    }

}