<?php

namespace Topxia\Service\Common;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Asm89\Twig\CacheExtension\CacheStrategyInterface;
use Asm89\Twig\CacheExtension\Exception\InvalidCacheLifetimeException;

class LifetimeCacheStrategy implements CacheStrategyInterface
{
    private $cache;

    public function __construct(CacheProviderInterface $cache)
    {
        $this->cache = $cache;
    }

    public function fetchBlock($key)
    {
        if ($this->isPageCacheEnabled()) {
            return $this->cache->fetch($key['key']);
        }
        return false;
    }

    public function generateKey($annotation, $value)
    {
        if (!is_numeric($value)) {
            throw new InvalidCacheLifetimeException($value);
        }

        return array(
            'lifetime' => $value,
            'key'      => '__LCS__'.$annotation
        );
    }

    public function saveBlock($key, $block)
    {
        if ($this->isPageCacheEnabled()) {
            return $this->cache->save($key['key'], $block, $key['lifetime']);
        }
    }

    protected function isPageCacheEnabled()
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService')->get('performance', array());
        return empty($setting['pageCache']) ? 0 : $setting['pageCache'];
    }
}
