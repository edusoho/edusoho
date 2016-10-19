<?php
namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\EventDispatcher\EventDispatcher;

class SlotManager
{
    protected $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function fire($name, $args)
    {
        $event = $event = new SlotEvent($args);
        $this->dispatcher->dispatch($name, $event);
        return $event->getContents();
    }

    public function on()
    {

    }

}