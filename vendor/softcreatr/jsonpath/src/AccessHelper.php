<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath;

use ArrayAccess;

use function abs;
use function array_key_exists;
use function array_keys;
use function array_slice;
use function array_values;
use function get_object_vars;
use function is_array;
use function is_int;
use function is_object;
use function method_exists;
use function property_exists;

class AccessHelper
{
    /**
     * @param array|ArrayAccess $collection
     */
    public static function collectionKeys($collection): array
    {
        if (is_object($collection)) {
            return array_keys(get_object_vars($collection));
        }

        return array_keys($collection);
    }

    /**
     * @param array|ArrayAccess $collection
     */
    public static function isCollectionType($collection): bool
    {
        return is_array($collection) || is_object($collection);
    }

    /**
     * @param array|ArrayAccess $collection
     */
    public static function keyExists($collection, $key, bool $magicIsAllowed = false): bool
    {
        if ($magicIsAllowed && is_object($collection) && method_exists($collection, '__get')) {
            return true;
        }

        if (is_int($key) && $key < 0) {
            $key = abs($key);
        }

        if (is_array($collection)) {
            return array_key_exists($key, $collection);
        }

        if ($collection instanceof ArrayAccess) {
            return $collection->offsetExists($key);
        }

        if (is_object($collection)) {
            return property_exists($collection, (string)$key);
        }

        return false;
    }

    /**
     * @todo Optimize conditions
     *
     * @param array|ArrayAccess $collection
     * @noinspection NotOptimalIfConditionsInspection
     */
    public static function getValue($collection, $key, bool $magicIsAllowed = false)
    {
        $return = null;

        if (
            $magicIsAllowed &&
            is_object($collection) &&
            !$collection instanceof ArrayAccess && method_exists($collection, '__get')
        ) {
            $return = $collection->__get($key);
        } elseif (is_object($collection) && !$collection instanceof ArrayAccess) {
            $return = $collection->$key;
        } elseif ($collection instanceof ArrayAccess) {
            $return = $collection->offsetGet($key);
        } elseif (is_array($collection)) {
            if (is_int($key) && $key < 0) {
                $return = array_slice($collection, $key, 1, false)[0];
            } else {
                $return = $collection[$key];
            }
        } elseif (is_int($key)) {
            $return = self::getValueByIndex($collection, $key);
        } else {
            $return = $collection[$key];
        }

        return $return;
    }

    /**
     * Find item in php collection by index
     * Written this way to handle instances ArrayAccess or Traversable objects
     *
     * @param array|ArrayAccess $collection
     *
     * @return mixed|null
     */
    private static function getValueByIndex($collection, $key)
    {
        $i = 0;

        foreach ($collection as $val) {
            if ($i === $key) {
                return $val;
            }

            ++$i;
        }

        if ($key < 0) {
            $total = $i;
            $i = 0;

            foreach ($collection as $val) {
                if ($i - $total === $key) {
                    return $val;
                }

                ++$i;
            }
        }

        return null;
    }

    /**
     * @param array|ArrayAccess $collection
     */
    public static function setValue(&$collection, $key, $value)
    {
        if (is_object($collection) && !$collection instanceof ArrayAccess) {
            return $collection->$key = $value;
        }

        if ($collection instanceof ArrayAccess) {
            return $collection->offsetSet($key, $value);
        }

        return $collection[$key] = $value;
    }

    /**
     * @param array|ArrayAccess $collection
     */
    public static function unsetValue(&$collection, $key): void
    {
        if (is_object($collection) && !$collection instanceof ArrayAccess) {
            unset($collection->$key);
        }

        if ($collection instanceof ArrayAccess) {
            $collection->offsetUnset($key);
        }

        if (is_array($collection)) {
            unset($collection[$key]);
        }
    }

    /**
     * @param array|ArrayAccess $collection
     *
     * @throws JSONPathException
     */
    public static function arrayValues($collection): array
    {
        if (is_array($collection)) {
            return array_values($collection);
        }

        if (is_object($collection)) {
            return array_values((array)$collection);
        }

        throw new JSONPathException("Invalid variable type for arrayValues");
    }
}
