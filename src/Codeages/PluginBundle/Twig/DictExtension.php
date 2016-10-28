<?php
namespace Codeages\PluginBundle\Twig;

class DictExtension extends \Twig_Extension
{
    protected $collector;

    public function __construct($collector)
    {
        $this->collector = $collector;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('dict', array($this, 'getDict')),
            new \Twig_SimpleFunction('dict_text', array($this, 'getDictText'), array('is_safe' => array('html'))),
        );
    }

    public function getDict($name)
    {
        return $this->collector->getDictMap($name);
    }

    public function getDictText($name, $key, $default = '')
    {
        return $this->collector->getDictText($name, $key, $default);
    }

    public function getName()
    {
        return 'codeages_plugin_dict_extension';
    }
}
