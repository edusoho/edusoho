<?php

namespace AppBundle\Twig;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Asm89\Twig\CacheExtension\CacheStrategyInterface;
use Asm89\Twig\CacheExtension\Exception\InvalidCacheLifetimeException;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;

class LifetimeCacheStrategy implements CacheStrategyInterface
{
    /**
     * @var CacheProviderInterface
     */
    private $cache;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(Biz $biz, CacheProviderInterface $cache)
    {
        $this->biz = $biz;
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
            'key' => '__LCS__'.$annotation,
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
        $setting = $this->getSettingService()->get('performance', array());

        return empty($setting['pageCache']) ? 0 : $setting['pageCache'];
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
