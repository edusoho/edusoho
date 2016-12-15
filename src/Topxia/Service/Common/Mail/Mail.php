<?php
namespace Topxia\Service\Common\Mail;

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

    protected function setting($name, $default = '')
    {
        
        $names = explode('.', $name);

        $name = array_shift($names);

        if (empty($name)) {
            return $default;
        }

        $value = $this->getSettingService()->get($name,$default);

        if (!isset($value)) {
            return $default;
        }

        if (empty($names)) {
            return $value;
        }

        $result = $value;

        foreach ($names as $name) {
            if (!isset($result[$name])) {
                return $default;
            }

            $result = $result[$name];
        }

        return $result;
    }

    public abstract function send();

    protected function getSettingService()
    {
        return $this->getKernel()->createService('System.SettingService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
