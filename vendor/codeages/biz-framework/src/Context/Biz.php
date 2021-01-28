<?php

namespace Codeages\Biz\Framework\Context;

use Codeages\Biz\Framework\Dao\Annotation\MetadataReader;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Codeages\Biz\Framework\Dao\RedisCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\ArrayStorage;
use Codeages\Biz\Framework\Service\ServiceProxy;

class Biz extends Container
{
    protected $providers = array();
    protected $booted = false;

    public function __construct(array $values = array())
    {
        parent::__construct();

        $biz = $this;

        $biz['debug'] = false;
        $biz['logger'] = null;
        $biz['interceptors'] = new \ArrayObject();
        $biz['migration.directories'] = new \ArrayObject();
        $biz['console.commands'] = new \ArrayObject();

        $biz['console.commands'][] = function () {
            return new \Codeages\Biz\Framework\Command\EnvWriteCommand();
        };

        $biz['autoload.aliases'] = new \ArrayObject(array('' => 'Biz'));

        $biz['dispatcher'] = function () {
            return new EventDispatcher();
        };

        $biz['callback_resolver'] = function ($biz) {
            return new CallbackResolver($biz);
        };

        $biz['autoloader'] = function ($biz) {
            return new ContainerAutoloader(
                $biz,
                $biz['autoload.aliases'],
                array(
                    'service' => $biz['autoload.object_maker.service'],
                    'dao' => $biz['autoload.object_maker.dao'],
                )
            );
        };

        $biz['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $className = "{$namespace}\\Service\\Impl\\{$name}Impl";

                if (!empty($biz['service_proxy_enabled'])) {
                    return new ServiceProxy($biz, $className);
                }

                return new $className($biz);
            };
        };

        $biz['autoload.object_maker.dao'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";

                return new DaoProxy($biz, new $class($biz), $biz['dao.metadata_reader'], $biz['dao.serializer'], $biz['dao.cache.array_storage']);
            };
        };

        $biz['array_storage'] = function () {
            return new ArrayStorage();
        };

        $biz['dao.metadata_reader'] = function ($biz) {
            if ($biz['debug']) {
                $cacheDirectory = null;
            } else {
                $cacheDirectory = $biz['cache_directory'].DIRECTORY_SEPARATOR.'dao_metadata';
            }

            return new MetadataReader($cacheDirectory);
        };

        $biz['dao.serializer'] = function () {
            return new FieldSerializer();
        };

        $biz['dao.cache.redis_wrapper'] = function ($biz) {
            return new RedisCache($biz['redis'], $biz['dispatcher']);
        };

        $biz['dao.cache.array_storage'] = null;
        $biz['dao.cache.enabled'] = false;

        $biz['dao.cache.strategy.default'] = function ($biz) {
            return $biz['dao.cache.strategy.table'];
        };

        $biz['dao.cache.strategy.table'] = function ($biz) {
            return new CacheStrategy\TableStrategy($biz['dao.cache.redis_wrapper'], $biz['dao.cache.array_storage']);
        };

        $biz['dao.cache.strategy.row'] = function ($biz) {
            return new CacheStrategy\RowStrategy($biz['dao.cache.redis_wrapper'], $biz['dao.metadata_reader']);
        };

        $biz['lock.flock.directory'] = null;

        $biz['lock.store'] = function ($biz) {
            return new \Symfony\Component\Lock\Store\FlockStore($biz['lock.flock.directory']);
        };

        $biz['lock.factory'] = function ($biz) {
            return new \Symfony\Component\Lock\Factory($biz['lock.store']);
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
