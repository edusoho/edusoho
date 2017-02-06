<?php
namespace Topxia\Service\Common\Mail;

use Topxia\Common\SettingToolkit;
use Topxia\Service\Common\ServiceKernel;

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
        if ('options' == $name) {
            return $this->options;
        }

        if (!array_key_exists($name, $this->options)) {
            return null;
        };

        return $this->options[$name];
    }

    public function __unset($name)
    {
        unset($this->options[$name]);
        return $this;
    }

    protected function setting($name, $default = '')
    {
        return SettingToolkit::getSetting($name, $default);
    }

    public abstract function send();

    protected function parseTemplate($options)
    {
        return TemplateToolkit::parseTemplate($options);
    }

    protected function getSettingService()
    {
        return $this->getKernel()->createService('System.SettingService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
