<?php
namespace Codeages\PluginBundle\System\Slot;

interface SlotInjectionInterface
{
    public function inject();

    public function setArguments($arguments = array());
}