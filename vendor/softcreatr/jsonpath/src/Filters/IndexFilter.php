<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Filters;

use Flow\JSONPath\{AccessHelper, JSONPathException};

class IndexFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection): array
    {
        if (is_array($this->token->value)) {
            $result = [];
            foreach ($this->token->value as $value) {
                if (AccessHelper::keyExists($collection, $value, $this->magicIsAllowed)) {
                    $result[] = AccessHelper::getValue($collection, $value, $this->magicIsAllowed);
                }
            }
            return $result;
        }

        if (AccessHelper::keyExists($collection, $this->token->value, $this->magicIsAllowed)) {
            return [
                AccessHelper::getValue($collection, $this->token->value, $this->magicIsAllowed),
            ];
        }

        if ($this->token->value === '*') {
            return AccessHelper::arrayValues($collection);
        }

        if ($this->token->value === 'length') {
            return [
                count($collection),
            ];
        }

        return [];
    }
}
