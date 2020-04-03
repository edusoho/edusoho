<?php

namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SlotInjection implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function setArgements($argements)
    {
        foreach ($argements as $name => $value) {
            $this->{$name} = $value;
        }
    }
}
