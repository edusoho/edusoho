<?php

namespace Codeages\Weblib\Routing;

class SilexMount implements Mount
{
    protected $app;

    protected $providers = [];

    protected $currentProvider = null;

    protected $booted = false;

    public function __construct($app, $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    public function mount(RoutingProvider $provider)
    {
        $collections = [];
        foreach ($provider->getRoutes() as $routeName => $route) {
            list($method, $uri, $controller) = $route;
            if (strpos($controller, ':') > 0) {
                list($controller, $action) = explode(':', $controller);
            } else {
                $action = strtolower($method);
            }

            $class = $provider->getNamespace().'\\'.$controller;
            $this->app[$class] = function () use ($provider, $class) {
                $resource = new $class();
                $injections = $provider->getInjections();
                foreach ($injections as $key => $value) {
                    $injectMethod = 'set'.ucfirst($key);
                    $resource->{$injectMethod}($value);
                }

                return $resource;
            };

            $route = call_user_func([$this->app, $method], $provider->getEndpoint().$uri, "{$class}:{$action}");
            if (is_string($routeName)) {
                $route->bind($routeName);
            }
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

        $uri = $this->request->getPathInfo();
        foreach ($this->providers as $provider) {
            if (strpos($uri, $provider->getEndpoint().'/') === 0) {
                $this->currentProvider = $provider;

                return $provider;
            }
        }

        return null;
    }
}
