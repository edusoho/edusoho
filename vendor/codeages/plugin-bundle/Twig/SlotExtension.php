<?php

namespace Codeages\PluginBundle\Twig;

class SlotExtension extends \Twig_Extension
{
    protected $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('slot',  array($this,'slot')),
        );
    }

    public function slot($name, $args)
    {
        return $this->manager->fire($name, $args);
    }

    public function getName()
    {
        return 'codeages_plugin_slot_extension';
    }
}
