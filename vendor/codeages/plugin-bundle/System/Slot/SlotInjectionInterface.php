<?php

namespace Codeages\PluginBundle\System\Slot;

interface SlotInjectionInterface
{
    public function inject();

    public function setArgements($argements = array());
}
