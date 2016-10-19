<?php

namespace Codeages\PluginBundle\Twig;

class SlotExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('slot', array($this, 'slot')),
        );
    }

    public function slot()
    {
        
    }

    public function getName()
    {
        return 'slot_extension';
    }
}
