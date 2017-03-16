<?php
namespace Topxia\Service\Util;

class HTMLPurifier
{
    protected $trusted;

    protected $engine;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function purify($html, $trusted = false)
    {
        $purifier = $this->getPurifyEngine($trusted);
        $html = $purifier->purify($html);
        if (!$trusted) {
            return $html;
        }

        $styles = $purifier->context->get('StyleBlocks');
        if ($styles) {
            $html = implode("\n", array(
                '<style>',
                implode("\n", $styles),
                '</style>',
                $html
            ));
        }

        return $html;
    }

    private function getPurifyEngine($trusted = false)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $this->config['cacheDir']);

        if ($trusted) {
            $config->set('Filter.ExtractStyleBlocks', true);
            $config->set('Attr.EnableID', true);
            $config->set('HTML.SafeObject', true);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(.*?)%'); 
            $config->set('Output.FlashCompat', true);
            $config->set('HTML.FlashAllowFullScreen', true);
        }

        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

        return  new \HTMLPurifier($config);
    }

}