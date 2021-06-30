<?php

namespace Biz\SCRM;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SCRMProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        /*
         * @param $biz
         * @return GoodsMediatorFactory
         */
        $biz['scrm_goods_mediator_factory'] = function ($biz) {
            return new GoodsMediatorFactory($biz);
        };
    }
}
