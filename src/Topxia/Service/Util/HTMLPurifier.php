<?php
namespace Topxia\Service\Util;

use Topxia\Service\Common\ServiceException;

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
                '<style type="text/css">',
                implode("\n", $styles),
                '</style>',
                $html
            ));
        }

        return $html;
    }

    private function getPurifyEngine($trusted = false)
    {
        if (!isset($this->config['cacheDir'])) {
            throw new ServiceException('Please give `cacheDir` argument.');
        }
        $this->warmUp($this->config['cacheDir']);

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $this->config['cacheDir']);

        if ($trusted) {
            $config->set('Filter.ExtractStyleBlocks', true);
            $config->set('Attr.EnableID', true);
            $config->set('HTML.SafeEmbed', true);
            $config->set('HTML.SafeObject', true);
            $config->set('Output.FlashCompat', true);
            $config->set('HTML.FlashAllowFullScreen', true);

            $safeIframeRegexp = $this->buildSafeIframeRegexp();
            if ($safeIframeRegexp) {
                $config->set('HTML.SafeIframe', true);
                $config->set('URI.SafeIframeRegexp', $safeIframeRegexp);
            }
        }

        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

        return  new \HTMLPurifier($config);
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

    private function buildSafeIframeRegexp()
    {
        if (empty($this->config['safeIframeDomains']) || !is_array($this->config['safeIframeDomains'])) {
            return null;
        }
        return '%^https?://('.implode('|', $this->config['safeIframeDomains']).')%';
    }

}