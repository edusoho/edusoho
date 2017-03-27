<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AsseticBundle\Controller;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;

/**
 * This is a special filter that has a noop on its load and dump methods, but
 * implements the HashableInterface, allowing it to be used with the AssetCache
 * to include an additional cache key component.
 */
class AssetCacheKeyFilter implements HashableInterface, FilterInterface
{
    private $cacheKey;

    /**
     * @param string $cacheKey A string to use as a cache key component
     */
    public function __construct($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * {@inheritdoc}
     */
    public function hash()
    {
        return $this->cacheKey;
    }

    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
