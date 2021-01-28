<?php

namespace Codeages\PluginBundle\System\Slot;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class SlotInjectionCollector
{
    protected $injections = array();

    protected $serial = PHP_INT_MAX;

    public function __construct(array $files, $cacheDir, $debug)
    {
        $cacheFile = $cacheDir.'/slot.php';
        $cache = new ConfigCache($cacheFile, $debug);

        if ($cache->isFresh() === false) {
            $resources = array();
            $slots = array();

            foreach ($files as $file) {
                $resources[] = new FileResource($file);
                $injections = Yaml::parse(file_get_contents($file));
                if (empty($injections) || !is_array($injections)) {
                    $injections = array();
                }
                $this->mergeInjections($injections);
            }

            $this->writeCacheFile($cache, $resources);
        } else {
            $this->injections = require $cacheFile;
        }
    }

    protected function mergeInjections($injections = array())
    {
        foreach ($injections as $injection) {
            if (!isset($this->injections[$injection['name']])) {
                $this->injections[$injection['name']] = new \SplPriorityQueue();
            }

            if (!isset($injection['priority'])) {
                $injection['priority'] = 0;
            }

            $this->injections[$injection['name']]->insert($injection, array($injection['priority'], $this->serial--));
        }
    }

    protected function writeCacheFile($cache, $resources)
    {
        $injections = array();

        foreach ($this->injections as $name => $priorityInjections) {
            foreach ($priorityInjections as $injection) {
                $injections[$name][] = $injection['class'];
            }
        }

        $this->injections = $injections;

        $cache->write(sprintf('<?php return %s;', var_export($injections, true)), $resources);
    }

    public function getInjections($name)
    {
        if (!isset($this->injections[$name])) {
            return array();
        }

        return $this->injections[$name];
    }
}
