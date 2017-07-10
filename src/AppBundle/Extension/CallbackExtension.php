<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use AppBundle\Controller\Callback\CloudSearch\CloudSearchProcessor;
use AppBundle\Controller\Callback\Marketing\MarketingProcessor;

class CallbackExtension extends Extension implements ServiceProviderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function register(Container $container)
    {
        $container['callback.cloud_search_processor'] = function ($biz) {
            $instance = new CloudSearchProcessor();
            $instance->setBiz($biz);

            return $instance;
        };

        $container['callback.marketing'] = function () {
            $instance = new MarketingProcessor($this->container);

            return $instance;
        };
    }

    public function getCallbacks()
    {
        return array(
            'cloud_search' => 'callback.cloud_search_processor',
            'marketing' => 'callback.marketing',
        );
    }
}
