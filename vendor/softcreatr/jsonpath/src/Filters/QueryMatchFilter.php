<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use RuntimeException;

use function explode;
use function in_array;
use function is_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function strpos;
use function strtolower;
use function substr;

class QueryMatchFilter extends AbstractFilter
{
    protected const MATCH_QUERY_OPERATORS = '
      @(\.(?<key>[^\s<>!=]+)|\[["\']?(?<keySquare>.*?)["\']?\])
      (\s*(?<operator>==|=~|=|<>|!==|!=|>=|<=|>|<|in|!in|nin)\s*(?<comparisonValue>.+))?
    ';

    /**
     * @inheritDoc
     */
    public function filter($collection): array
    {
        preg_match('/^' . static::MATCH_QUERY_OPERATORS . '$/x', $this->token->value, $matches);

        if (!isset($matches[1])) {
            throw new RuntimeException('Malformed filter query');
        }

        $key = $matches['key'] ?: $matches['keySquare'];

        if ($key === '') {
            throw new RuntimeException('Malformed filter query: key was not set');
        }

        $operator = $matches['operator'] ?? null;
        $comparisonValue = $matches['comparisonValue'] ?? null;

        if (is_string($comparisonValue)) {
            if (strpos($comparisonValue, "[") === 0 && substr($comparisonValue, -1) === "]") {
                $comparisonValue = substr($comparisonValue, 1, -1);
                $comparisonValue = preg_replace('/^[\'"]/', '', $comparisonValue);
                $comparisonValue = preg_replace('/[\'"]$/', '', $comparisonValue);
                $comparisonValue = preg_replace('/[\'"],[ ]*[\'"]/', ',', $comparisonValue);
                $comparisonValue = explode(",", $comparisonValue);
            } else {
                $comparisonValue = preg_replace('/^[\'"]/', '', $comparisonValue);
                $comparisonValue = preg_replace('/[\'"]$/', '', $comparisonValue);

                if (strtolower($comparisonValue) === 'false') {
                    $comparisonValue = false;
                } elseif (strtolower($comparisonValue) === 'true') {
                    $comparisonValue = true;
                } elseif (strtolower($comparisonValue) === 'null') {
                    $comparisonValue = null;
                }
            }
        }

        $return = [];

        foreach ($collection as $value) {
            if (AccessHelper::keyExists($value, $key, $this->magicIsAllowed)) {
                $value1 = AccessHelper::getValue($value, $key, $this->magicIsAllowed);

                if ($operator === null && $value1) {
                    $return[] = $value;
                }

                /** @noinspection TypeUnsafeComparisonInspection */
                // phpcs:ignore -- This is a loose comparison by design.
                if (($operator === '=' || $operator === '==') && $value1 == $comparisonValue) {
                    $return[] = $value;
                }

                /** @noinspection TypeUnsafeComparisonInspection */
                // phpcs:ignore -- This is a loose comparison by design.
                if (($operator === '!=' || $operator === '!==' || $operator === '<>') && $value1 != $comparisonValue) {
                    $return[] = $value;
                }

                if ($operator === '=~' && @preg_match($comparisonValue, $value1)) {
                    $return[] = $value;
                }

                if ($operator === '>' && $value1 > $comparisonValue) {
                    $return[] = $value;
                }

                if ($operator === '>=' && $value1 >= $comparisonValue) {
                    $return[] = $value;
                }

                if ($operator === '<' && $value1 < $comparisonValue) {
                    $return[] = $value;
                }

                if ($operator === '<=' && $value1 <= $comparisonValue) {
                    $return[] = $value;
                }

                if ($operator === 'in' && is_array($comparisonValue) && in_array($value1, $comparisonValue, true)) {
                    $return[] = $value;
                }

                if (
                    ($operator === 'nin' || $operator === '!in') &&
                    is_array($comparisonValue) &&
                    !in_array($value1, $comparisonValue, true)
                ) {
                    $return[] = $value;
                }
            }
        }

        return $return;
    }
}
