<?php


namespace Biz;


use Biz\Common\HTMLHelper;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helper'] = new HTMLHelper($biz);
    }

}