<?php

namespace Codeages\PluginBundle\Slot;

use Codeages\PluginBundle\System\Slot\SlotInjection;

class ExampleHeaderSlot extends SlotInjection
{
    public function inject()
    {
        // return $this->container->get('twig')->render()
        return 'ExampleHeaderSlot inject';
    }
}
