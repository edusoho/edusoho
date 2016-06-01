<?php
namespace Topxia\Service\Common;

abstract class Mail
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function __set($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function __get($name)
    {
        if(!array_key_exists($name, $this->options)){
            return null;
        };

        return $this->options[$name];
    }

    public function __unset($name)
    {
        unset($this->options[$name]);
        return $this;
    }

    protected function setting($name, $default)
    {
        global $kernel;
        $container = $kernel->getContainer();
        return $container->get('topxia.twig.web_extension')->getSetting($name, $default);
    }

    public abstract function send();
}
