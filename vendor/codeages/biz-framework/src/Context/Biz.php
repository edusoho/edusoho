<?php

namespace Codeages\Biz\Framework\Context;

use Codeages\Biz\Framework\Dao\DaoProxy;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Biz extends Container
{
    protected $providers = array();
    protected $booted = false;

    public function __construct(array $values = array())
    {
        parent::__construct();

        $this['debug'] = false;
        $this['logger'] = null;
        $this['migration.directories'] = new \ArrayObject();

        $this['autoload.aliases'] = new \ArrayObject(array('' => 'Biz'));

        $this['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";

                return new $class($biz);
            };
        };

        $this['autoload.object_maker.dao'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";

                return new DaoProxy($biz, new $class($biz));
            };
        };

        $this['autoloader'] = function ($biz) {
            return new ContainerAutoloader($biz, $biz['autoload.aliases'], array(
                'service' => $biz['autoload.object_maker.service'],
                'dao' => $biz['autoload.object_maker.dao'],
            ));
        };

        $this['dispatcher'] = function ($biz) {
            return new EventDispatcher();
        };

        $this['callback_resolver'] = function ($biz) {
            return new CallbackResolver($biz);
        };

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;
        parent::register($provider, $values);

        return $this;
    }

    public function boot($options = array())
    {
        if (true === $this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            if ($provider instanceof EventListenerProviderInterface) {
                $provider->subscribe($this, $this['dispatcher']);
            }

            if ($provider instanceof BootableProviderInterface) {
                $provider->boot($this);
            }
        }

        $this->booted = true;
    }

    public function on($eventName, $callback, $priority = 0)
    {
        if ($this->booted) {
            $this['dispatcher']->addListener($eventName, $this['callback_resolver']->resolveCallback($callback), $priority);

            return;
        }

        $this->extend('dispatcher', function (EventDispatcherInterface $dispatcher, $app) use ($callback, $priority, $eventName) {
            $dispatcher->addListener($eventName, $app['callback_resolver']->resolveCallback($callback), $priority);

            return $dispatcher;
        });
    }

    public function service($alias)
    {
        return $this['autoloader']->autoload('service', $alias);
    }

    public function dao($alias)
    {
        return $this['autoloader']->autoload('dao', $alias);
    }
}
