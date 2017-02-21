<?php
namespace Codeages\PluginBundle\System\Slot;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SlotManager
{
    protected $cache;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SlotInjectionCollector
     */
    protected $collector;

    public function __construct(SlotInjectionCollector $collector, ContainerInterface $container)
    {
        $this->collector = $collector;
        $this->container = $container;
    }

    public function fire($name, $args)
    {
        if(isset($this->cache[$name])){
            return $this->cache[$name];
        }

        $injections = $this->collector->getInjections($name);
        if (empty($injections)) {
            return '';
        }

        $contents = array();

        foreach ($injections as $name => $class) {
            $injection = new $class();

            foreach ($args as $argName => $argValue) {
                $injection->setContainer($this->container);
                $injection->setArgements($args);
            }

            $contents[] = $injection->inject();
        }

        return $this->cache[$name] = implode('', $contents);
    }
}