<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use AppBundle\Controller\Callback\CloudSearch\CloudSearchProcessor;

class CallbackExtension extends Extension implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['callback.cloud_search_processor'] = function ($biz) {
            $instance = new CloudSearchProcessor();
            $instance->setBiz($biz);

            return $instance;
        };
    }

    public function getCallbacks()
    {
        return array(
            'cloud_search' => 'callback.cloud_search_processor',
        );
    }
}
