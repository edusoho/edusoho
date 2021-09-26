<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath\Filters;

use ArrayAccess;
use Flow\JSONPath\AccessHelper;
use Flow\JSONPath\JSONPathException;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection)
    {
        $result = [];

        $this->recurse($result, $collection);

        return $result;
    }

    /**
     * @param array $result
     * @param array|ArrayAccess $data
     *
     * @throws JSONPathException
     */
    private function recurse(array &$result, $data)
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
