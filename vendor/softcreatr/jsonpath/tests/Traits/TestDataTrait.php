<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Test\Traits;

use ArrayAccess;
use RuntimeException;

trait TestDataTrait
{
    /**
     * Returns decoded JSON from a given file either as array or object.
     *
     * @param bool|int $asArray
     *
     * @return array|ArrayAccess|null
     */
    protected function getData(string $type, $asArray = true)
    {
        $filePath = sprintf('%s/data/%s.json', dirname(__DIR__, 1), $type);

        if (!file_exists($filePath)) {
            throw new RuntimeException("File {$filePath} does not exist.");
        }

        $json = json_decode(file_get_contents($filePath), (bool)$asArray);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("File {$filePath} does not contain valid JSON.");
        }

        return $json;
    }
}
