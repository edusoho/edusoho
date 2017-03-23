<?php
namespace Topxia\Service\Common\Mail;

use Topxia\Common\SettingToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\Exception\AccessDeniedException;

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

    public function send()
    {
        $this->mailCheckRatelimiter();
        return $this->doSend();
    }

    protected function mailCheckRatelimiter()
    {
        $biz = $this->getKernel()->getBiz();

        $factory = $biz['ratelimiter.factory'];
        $limiter = $factory('email_'.$this->options['template'], 5, 1800);
        $remain = $limiter->check($this->to);
        if ($remain == 0) {
            throw new AccessDeniedException($this->getKernel()->trans('操作过于频繁，请30分钟之后再试'));
        }
    }

    public abstract function doSend();

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
