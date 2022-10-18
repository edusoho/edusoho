<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Filters;

use Flow\JSONPath\{AccessHelper, JSONPathException};
use ArrayAccess;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection): array
    {
        $result = [];

        $this->recurse($result, $collection);

        return $result;
    }

    /**
     * @param array|ArrayAccess $data
     *
     * @throws JSONPathException
     */
    private function recurse(array &$result, $data): void
    {
        $result[] = $data;

        if (AccessHelper::isCollectionType($data)) {
            foreach (AccessHelper::arrayValues($data) as $key => $value) {
                $results[] = $value;

                if (AccessHelper::isCollectionType($value)) {
                    $this->recurse($result, $value);
                }
            }
        }
    }
}
