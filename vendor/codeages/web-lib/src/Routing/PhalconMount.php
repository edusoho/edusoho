<?php

namespace Codeages\Weblib\Routing;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;

class PhalconMount implements Mount
{
    /**
     * @var Micro
     */
    protected $app;

    protected $providers = [];

    protected $currentProvider = null;

    protected $booted = false;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function mount(RoutingProvider $provider)
    {
        $collections = [];
        foreach ($provider->getRoutes() as $route) {
            list($method, $uri, $controller) = $route;
            if (strpos($controller, ':') > 0) {
                list($controller, $action) = explode(':', $controller);
            } else {
                $action = strtolower($method);
            }

            if (!isset($collections[$controller])) {
                $collection = new Collection();
                $collection->setHandler($provider->getNamespace().'\\'.$controller, true);
                $collections[$controller] = $collection;
            }

            $uri = rtrim($provider->getEndpoint(), "\/").rtrim($uri, "\/");

            call_user_func([$collections[$controller], $method], $uri ? : '/', $action);
        }


        foreach ($collections as $collection) {
            $this->app->mount($collection);
        }

        $this->providers[] = $provider;
    }

    public function boot()
    {
        $provider = $this->getCurrentProvider();
        if ($provider) {
            $provider->registerHandlers();
        }

        $this->booted = true;
    }

    protected function getCurrentProvider()
    {
        if ($this->booted) {
            return $this->currentProvider;
        }

        $uri = $this->app['request']->getURI();
        foreach ($this->providers as $provider) {
            if (strpos($uri, rtrim($provider->getEndpoint(), "\/").'/') === 0) {
                $this->currentProvider = $provider;

                return $provider;
            }
        }

        return null;
    }
}
