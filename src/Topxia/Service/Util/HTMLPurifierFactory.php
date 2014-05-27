<?php

namespace Topxia\Service\Util;

class HTMLPurifierFactory
{
    
    protected $config;

    public function __construct ($config)
    {
        $this->config = $config;
    }

    public function create ($trusted = false)
    {
        $this->warmUp($this->config['cacheDir']);
        
        $config = \HTMLPurifier_Config::createDefault();
        
        $config->set('Cache.SerializerPath', $this->config['cacheDir']);
        $config->set('CSS.AllowTricky', true);
        if ($trusted) {
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(.*?)%'); 
        }
        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

        
        return new \HTMLPurifier($config);
    }

    private function warmUp ($cacheDir)
    {
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        if (! is_writeable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }

}