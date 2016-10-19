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
        $content = call_user_func_array(array($this->controller, 'extend'), $event->getArguments());
        $event->addContent($content);
    }

}