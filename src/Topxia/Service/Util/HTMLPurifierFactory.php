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
        
        // $config->set('Cache.SerializerPath', $this->config['cacheDir']);
        $config->set('Attr.EnableID', true);
        $config->set('CSS.AllowTricky', true);
        if ($trusted) {
            // $config->set('HTML.Trusted', true);
            // $config->set('CSS.Trusted', true);
            $config->set('HTML.SafeObject', true);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(.*?)%'); 
            $config->set('Output.FlashCompat', true);
            $config->set('HTML.FlashAllowFullScreen', true);
            $config->set('Filter.ExtractStyleBlocks', true);
            $config->set('Filter.ExtractStyleBlocks.TidyImpl', false);
        }

        $config->set('HTML.TargetBlank', true);

        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

        
        return new \HTMLPurifier($config);
    }

    protected function warmUp ($cacheDir)
    {
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        if (! is_writeable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }

}