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
            new \Twig_SimpleFunction('slot', array($this, 'slot'), array('is_safe' => array('html'))),
        );
    }

    public function slot($name, $args = array())
    {
        return $this->manager->fire($name, $args);
    }

    public function getName()
    {
        return 'codeages_plugin_slot_extension';
    }
}
