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

    private $services = array();

    /**
     * @var ConfigCache
     */
    private $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $kernel = $this->container->get('kernel');
        $cacheFile = $kernel->getCacheDir() . DIRECTORY_SEPARATOR . '/event_map.php';
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

        if(isset($eventMap[$eventName])){
            return $eventMap[$eventName];
        }else{
            return array();
        }
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

    protected function getEventMap(){
        $this->generateCache();
        $eventMap = require $this->cache->getPath();
        return $eventMap;
    }

    public function generateCache()
    {
        if ($this->cache->isFresh()) {
            return;
        }

        $file = new FileResource($this->cache->getPath());

        $eventMap = array();

        foreach ($this->services as $service) {
            $class = $this->container->get($service);
            $events = forward_static_call(array($class, 'getSubscribedEvents'));
            foreach ($events as $eventName => $callback) {
                $eventMap[$eventName][] = array($service, $callback);
            }
        }

        $biz = $this->container->get('biz');
        foreach ($biz['subscribers'] as $subscriber) {
            $events = forward_static_call(array($subscriber, 'getSubscribedEvents'));
            foreach ($events as $eventName => $callback) {
                $eventMap[$eventName][] = array($subscriber, $callback);
            }
        }

        $this->cache->write(sprintf('<?php return %s;', var_export($eventMap, true)), array($file));
    }

}