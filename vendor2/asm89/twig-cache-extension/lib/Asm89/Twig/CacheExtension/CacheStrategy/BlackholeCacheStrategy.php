<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\CacheStrategy;

use Asm89\Twig\CacheExtension\CacheStrategyInterface;

/**
 * CacheStrategy which doesn't cache at all
 *
 * This strategy can be used in development mode, e.g. editing twig templates,
 * to prevent previously cached versions from being rendered.
 *
 * @see     https://github.com/asm89/twig-cache-extension/pull/29
 *
 * @author  Hagen Hübel <hhuebel@itinance.com>
 *
 * @package Asm89\Twig\CacheExtension\CacheStrategy
 */
class BlackholeCacheStrategy implements CacheStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function fetchBlock($key)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($annotation, $value)
    {
        return microtime(true) . mt_rand();
    }

    /**
     * {@inheritDoc}
     */
    public function saveBlock($key, $block)
    {
        // fire and forget
    }
}