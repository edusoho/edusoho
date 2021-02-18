<?php

namespace Codeages\Biz\ItemBank\Util;

use Codeages\Biz\Framework\Service\Exception\ServiceException;

class HTMLPurifierFactory
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function create($trusted = false)
    {
        if (!isset($this->config['cacheDir'])) {
            throw new ServiceException('Please give `cacheDir` argument.');
        }
        $this->warmUp($this->config['cacheDir']);

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $this->config['cacheDir']);

        if ($trusted) {
            //    $config->set('HTML.Trusted', true);
            $config->set('Filter.ExtractStyleBlocks', true);
            $config->set('Attr.EnableID', true);
            $config->set('HTML.SafeEmbed', true);
            $config->set('HTML.SafeScripting', []);
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

        return new \HTMLPurifier($config);
    }

    public function createSimple()
    {
        if (!isset($this->config['cacheDir'])) {
            throw new ServiceException('Please give `cacheDir` argument.');
        }
        $this->warmUp($this->config['cacheDir']);

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $this->config['cacheDir']);
        $config->set('HTML.Trusted', true);

        return new \HTMLPurifier($config);
    }

    private function buildSafeIframeRegexp()
    {
        if (empty($this->config['safeIframeDomains']) || !is_array($this->config['safeIframeDomains'])) {
            return null;
        }

        return '%^https?://('.implode('|', $this->config['safeIframeDomains']).')%';
    }

    protected function warmUp($cacheDir)
    {
        if (!@mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
            throw new ServiceException('mkdir cache dir error');
        }

        if (!is_writable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }
}
