<?php

namespace Codeages\Biz\Framework\Context;

use Codeages\Biz\Framework\Dao\DaoProxy;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\CacheStrategy\SharedStorage;

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

        $this['dispatcher'] = function () {
            return new EventDispatcher();
        };

        $this['callback_resolver'] = function ($biz) {
            return new CallbackResolver($biz);
        };

        $this['autoloader'] = function ($biz) {
            return new ContainerAutoloader(
                $biz,
                $biz['autoload.aliases'],
                array(
                    'service' => $biz['autoload.object_maker.service'],
                    'dao' => $biz['autoload.object_maker.dao'],
                )
            );
        };

        $this['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";

                return new $class($biz);
            };
        };

        $this['autoload.object_maker.dao'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";

                return new DaoProxy($biz, new $class($biz), $biz['dao.serializer']);
            };
        };

        $this['dao.serializer'] = function () {
            return new FieldSerializer();
        };

        $this['dao.cache.first.enabled'] = true;
        $this['dao.cache.second.enabled'] = false;

        $this['dao.cache.chain'] = $this->factory(function ($biz) {
            return new CacheStrategy\DoubleCacheStrategy();
        });

        $this['dao.cache.first'] = function () {
            return new CacheStrategy\MemoryCacheStrategy();
        };

        $this['dao.cache.second.strategy.default'] = function ($biz) {
            return $biz['dao.cache.second.strategy.table'];
        };

        $this['dao.cache.second.strategy.table'] = function ($biz) {
            return new CacheStrategy\TableCacheStrategy($biz['redis'], $biz['dao.cache.shared_storage']);
        };

        $this['dao.cache.shared_storage'] = function ($biz) {
            return new SharedStorage();
        };

        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;
        parent::register($provider, $values);

        return $this;
    }

    public function boot()
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
