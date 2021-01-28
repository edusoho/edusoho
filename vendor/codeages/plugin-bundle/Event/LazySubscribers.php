<?php

namespace Codeages\PluginBundle\Event;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LazySubscribers
{
    const DEFAULT_PRIORITY = 0;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $services = array();

    /**
     * @var ConfigCache
     */
    private $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $kernel = $this->container->get('kernel');
        $cacheFile = $kernel->getCacheDir().DIRECTORY_SEPARATOR.'event_map.php';
        $this->cache = new ConfigCache($cacheFile, $kernel->isDebug());
    }

    /**
     * return array(
     *    array(service|class, method)
     * )
     *
     * @param $eventName
     *
     * @return array
     */
    public function getCallbacks($eventName)
    {
        $eventMap = $this->getEventMap();

        if (isset($eventMap[$eventName])) {
            return $eventMap[$eventName];
        }

        return array();
    }

    /**
     * @param $service
     *
     * @return $this
     */
    public function addSubscriberService($service)
    {
        $this->services[] = $service;

        return $this;
    }

    protected function getEventMap()
    {
        $this->generateCache();

        return require $this->cache->getPath();
    }

    public function generateCache()
    {
        if ($this->cache->isFresh()) {
            return;
        }

        $file = new FileExistenceResource($this->cache->getPath());

        $eventMap = array();

        foreach ($this->services as $service) {
            /**
             * @var EventSubscriber
             */
            $class = $this->container->get($service);

            /**
             * @var array<string, string|array>
             */
            $events = forward_static_call(array($class, 'getSubscribedEvents'));

            foreach ($events as $eventName => $callbacks) {
                if (is_array($callbacks)) {
                    $eventMap[$eventName][] = array($service, $callbacks[0], $callbacks[1]);
                } else {
                    $eventMap[$eventName][] = array($service, $callbacks, self::DEFAULT_PRIORITY);
                }
            }
        }

        foreach ($eventMap as $eventName => &$callbacks) {
            uasort($callbacks, function ($x, $y) {
                if ($x[2] == $y[2]) {
                    return 0;
                }

                return ($x[2] > $y[2]) ? -1 : 1;
            });

            $callbacks = array_values($callbacks);
        }

        $this->cache->write(sprintf('<?php return %s;', var_export($eventMap, true)), array($file));
    }
}
