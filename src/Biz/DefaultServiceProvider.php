<?php


namespace Biz;


use Biz\Common\HTMLHelpful;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helpful'] = new HTMLHelpful($biz);
    }

}