<?php


namespace Codeages\PluginBundle\Event;


use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LazySubscribers
{
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

        $kernel      = $this->container->get('kernel');
        $cacheFile   = $kernel->getCacheDir().DIRECTORY_SEPARATOR.'/event_map.php';
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

        $file = new FileResource($this->cache->getPath());

        $eventMap = array();

        foreach ($this->services as $service) {
            /**
             * @var $class EventSubscriber
             */
            $class  = $this->container->get($service);

            /**
             * @var $events array<string, string|array>
             */
            $events = forward_static_call(array($class, 'getSubscribedEvents'));

            foreach ($events as $eventName => $callbacks) {
                if (is_array($callbacks)) {
                    array_walk($callbacks, function ($callback) use (&$eventMap, $eventName, $service) {
                        $eventMap[$eventName][] = array($service, $callback);
                    });
                } else {
                    $eventMap[$eventName][] = array($service, $callbacks);
                }
            }
        }

        $this->cache->write(sprintf('<?php return %s;', var_export($eventMap, true)), array($file));
    }

}