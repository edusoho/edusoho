<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\CacheProvider;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Adapter class to make extension interoperable with every PSR-6 adapter.
 *
 * @see http://php-cache.readthedocs.io/
 *
 * @author Rvanlaak <rvanlaak@gmail.com>
 */
class PsrCacheAdapter implements CacheProviderInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return mixed|false
     */
    public function fetch($key)
    {
        // PSR-6 implementation returns null, CacheProviderInterface expects false
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return $item->get();
        }
        return false;
    }

    /**
     * @param string $key
     * @param string $value
     * @param int|\DateInterval $lifetime
     * @return bool
     */
    public function save($key, $value, $lifetime = 0)
    {
        $item = $this->cache->getItem($key);
        $item->set($value);
        $item->expiresAfter($lifetime);

        return $this->cache->save($item);
    }

}
