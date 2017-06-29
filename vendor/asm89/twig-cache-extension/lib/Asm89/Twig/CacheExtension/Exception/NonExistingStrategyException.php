<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\Exception;

class NonExistingStrategyException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($strategyKey, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('No strategy configured with key "%s".', $strategyKey), $code, $previous);
    }
}
