<?php

namespace Biz\Mail\Template;

use AppBundle\Common\SettingToolkit;
use Codeages\Biz\Framework\Context\BizAware;

abstract class BaseTemplate extends BizAware
{
    protected function renderBody($view, $params)
    {
        $loader = new \Twig_Loader_Filesystem($this->biz['email_template_paths']);
        $twig = new \Twig_Environment($loader);

        return $twig->render($view, $params);
    }

    protected function getSiteName()
    {
        return $this->setting('site.name', 'EDUSOHO');
    }

    protected function setting($name, $default = '')
    {
        return SettingToolkit::getSetting($name, $default);
    }
}
