<?php

namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\DependencyInjection\ContainerAware;

class SlotInjection extends ContainerAware
{
    public function setArgements($argements)
    {
        foreach ($argements as $name => $value) {
            $this->{$name} = $value;
        }
    }
}
