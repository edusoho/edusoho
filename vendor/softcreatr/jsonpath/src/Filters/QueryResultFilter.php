<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Filters;

use Flow\JSONPath\{AccessHelper, JSONPathException};

use function count;
use function preg_match;

class QueryResultFilter extends AbstractFilter
{
    /**
     * @inheritDoc
     *
     * @throws JSONPathException
     */
    public function filter($collection): array
    {
        preg_match('/@\.(?<key>\w+)\s*(?<operator>[-+*\/])\s*(?<numeric>\d+)/', $this->token->value, $matches);

        $matchKey = $matches['key'];

        if (AccessHelper::keyExists($collection, $matchKey, $this->magicIsAllowed)) {
            $value = AccessHelper::getValue($collection, $matchKey, $this->magicIsAllowed);
        } elseif ($matches['key'] === 'length') {
            $value = count($collection);
        } else {
            return [];
        }

        switch ($matches['operator']) {
            case '+':
                $resultKey = $value + $matches['numeric'];
                break;
            case '*':
                $resultKey = $value * $matches['numeric'];
                break;
            case '-':
                $resultKey = $value - $matches['numeric'];
                break;
            case '/':
                $resultKey = $value / $matches['numeric'];
                break;
            default:
                throw new JSONPathException('Unsupported operator in expression');
        }

        $result = [];

        if (AccessHelper::keyExists($collection, $resultKey, $this->magicIsAllowed)) {
            $result[] = AccessHelper::getValue($collection, $resultKey, $this->magicIsAllowed);
        }

        return $result;
    }
}
