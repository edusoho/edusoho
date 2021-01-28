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
use Asm89\Twig\CacheExtension\Exception\NonExistingStrategyException;
use Asm89\Twig\CacheExtension\Exception\NonExistingStrategyKeyException;

/**
 * Combines several configured cache strategies.
 *
 * Useful for combining for example generational cache strategy with a lifetime
 * cache strategy, but also useful when combining several generational cache
 * strategies which differ on cache lifetime (infinite, 1hr, 5m).
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class IndexedChainingCacheStrategy implements CacheStrategyInterface
{
    /**
     * @var CacheStrategyInterface[]
     */
    private $strategies;

    /**
     * @param array $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBlock($key)
    {
        return $this->strategies[$key['strategyKey']]->fetchBlock($key['key']);
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($annotation, $value)
    {
        if (!is_array($value) || null === $strategyKey = key($value)) {
            throw new NonExistingStrategyKeyException();
        }

        if (!isset($this->strategies[$strategyKey])) {
            throw new NonExistingStrategyException($strategyKey);
        }

        return array(
            'strategyKey' => $strategyKey,
            'key'         => $this->strategies[$strategyKey]->generateKey($annotation, current($value)),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function saveBlock($key, $block)
    {
        return $this->strategies[$key['strategyKey']]->saveBlock($key['key'], $block);
    }
}
