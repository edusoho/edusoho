<?php

namespace Codeages\Biz\Framework\Context;

use Codeages\Biz\Framework\Dao\DaoProxy\DaoProxy;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Dao\FieldSerializer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

        $this['dao.serializer'] = function () {
            return new FieldSerializer();
        };

        $this['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";

                return new $class($biz);
            };
        };

        $this['dao.proxy'] = $this->factory(function($biz) {
            return new DaoProxy($biz);
        });

        $this['autoload.object_maker.dao'] = function($biz) {
            return function($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";
                $dao = new $class($biz);
                $declares = $dao->declares();
                $daoProxy = $biz['dao.proxy'];
                $daoProxy->setDao($dao);
                return $daoProxy;
            };
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

        $this['dispatcher'] = function () {
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
