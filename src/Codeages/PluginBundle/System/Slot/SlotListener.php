<?php
namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\EventDispatcher\EventDispatcher;

class SlotListener
{
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    public function onExtend($event)
    {
        $content = $this->controller->extend($args);

        $event->addContent($content);
    }

}