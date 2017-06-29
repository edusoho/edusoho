<?php

namespace Codeages\PluginBundle\Twig;

class DictExtension extends \Twig_Extension
{
    protected $collector;
    protected $locale;
    protected $container;

    public function __construct($collector, $container)
    {
        $this->collector = $collector;
        $this->container = $container;
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
        $locale = $this->getLocale();

        return $this->collector->getDictMap($locale, $name);
    }

    public function getDictText($name, $key, $default = '')
    {
        $locale = $this->getLocale();

        return $this->collector->getDictText($locale, $name, $key, $default);
    }

    public function getName()
    {
        return 'codeages_plugin_dict_extension';
    }

    private function getLocale()
    {
        if (!$this->locale) {
            $locale = $this->container->get('request')->getLocale();
            $this->locale = $locale;
        }

        return $this->locale;
    }
}
