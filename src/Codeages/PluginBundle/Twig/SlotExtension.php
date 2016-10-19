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

    public function slot()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            throw new \InvalidArgumentException('The Twig function `slot` must have at least one argument, and the first argument is the name of the slot.');
        }

        $name = array_shift($args);

        $this->manager->fire($name, $args);





        var_dump($name, $args);

    }

    public function getName()
    {
        return 'slot_extension';
    }
}
