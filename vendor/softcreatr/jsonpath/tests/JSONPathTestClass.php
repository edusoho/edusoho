<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Test;

class JSONPathTestClass
{
    /**
     * @var string[]
     */
    protected $attributes = [
        'foo' => 'bar',
    ];

    /**
     * @param $key
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key): ?string
    {
        return $this->attributes[$key] ?? null;
    }
}
