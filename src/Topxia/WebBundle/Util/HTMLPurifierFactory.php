<?php

namespace Topxia\WebBundle\Util;

class HTMLPurifierFactory {
    
    protected $container;

    public function __construct ($container) {
        $this->container = $container;
    }

    public function warmUp ($cacheDir) {
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        if (! is_writeable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }

    public function get () {
        $path = $this->container->getParameter('kernel.root_dir') . '/../vendor/htmlpurifier/htmlpurifier-standalone/library/HTMLPurifier.standalone.php';
        require_once $path;
        
        $cacheDir = $this->container->getParameter('kernel.cache_dir') . '/htmlpurifier';
        $this->warmUp($cacheDir);
        
        $config = \HTMLPurifier_Config::createDefault();
        
        $config->set('Cache.SerializerPath', $cacheDir);
        $config->set('CSS.AllowTricky', true);
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        
        return new \HTMLPurifier($config);
    }

}