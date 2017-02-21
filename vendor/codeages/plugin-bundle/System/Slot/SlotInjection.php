<?php
namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\DependencyInjection\ContainerAware;

abstract class SlotInjection extends ContainerAware implements SlotInjectionInterface
{
    public function setArguments($arguments)
    {
        foreach ($arguments as $name => $value) {
            $this->{$name} = $value;
        }
    }
}