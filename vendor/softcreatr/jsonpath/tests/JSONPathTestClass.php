<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

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
     *
     * @return string|null
     */
    public function __get($key)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
    }
}
