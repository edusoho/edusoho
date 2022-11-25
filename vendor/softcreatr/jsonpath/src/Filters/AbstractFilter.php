<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Filters;

use ArrayAccess;
use Flow\JSONPath\{JSONPath, JSONPathToken};

abstract class AbstractFilter
{
    /**
     * @var JSONPathToken
     */
    protected $token;

    /**
     * @var  bool
     */
    protected $magicIsAllowed = false;

    /**
     * @param int|bool $options
     */
    public function __construct(JSONPathToken $token, $options = false)
    {
        $this->token = $token;
        $this->magicIsAllowed = (bool)($options & JSONPath::ALLOW_MAGIC);
    }

    /**
     * @param array|ArrayAccess $collection
     */
    abstract public function filter($collection): array;
}
