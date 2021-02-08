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
    protected $attributes = [
        'foo' => 'bar',
    ];

    /**
     * @param $key
     * @return string|null
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key): ?string
    {
        return $this->attributes[$key] ?? null;
    }
}
