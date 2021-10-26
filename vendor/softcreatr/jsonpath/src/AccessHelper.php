<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath;

use ArrayAccess;

class AccessHelper
{
    /**
     * @param array|ArrayAccess $collection
     *
     * @return array
     */
    public static function collectionKeys($collection)
    {
        if (is_object($collection)) {
            return array_keys(get_object_vars($collection));
        }

        return array_keys($collection);
    }

    /**
     * @param array|ArrayAccess $collection
     *
     * @return bool
     */
    public static function isCollectionType($collection)
    {
        return is_array($collection) || is_object($collection);
    }

    /**
     * @param array|ArrayAccess $collection
     * @param mixed $key
     * @param bool $magicIsAllowed
     *
     * @return bool
     */
    public static function keyExists($collection, $key, $magicIsAllowed = false)
    {
        if ($magicIsAllowed && is_object($collection) && method_exists($collection, '__get')) {
            return true;
        }

        if (is_int($key) && $key < 0) {
            $key = abs($key);
        }

        if (is_array($collection) || $collection instanceof ArrayAccess) {
            return array_key_exists($key, $collection);
        }

        if (is_object($collection)) {
            return property_exists($collection, (string)$key);
        }

        return false;
    }

    /**
     * @param array|ArrayAccess $collection
     * @param mixed $key
     * @param bool $magicIsAllowed
     *
     * @return mixed|null
     * @noinspection NotOptimalIfConditionsInspection
     *
     * @todo Optimize conditions
     */
    public static function getValue($collection, $key, $magicIsAllowed = false)
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
     * @param mixed $key
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
     * @param mixed $key
     * @param mixed $value
     *
     * @return mixed
     */
    public static function setValue(&$collection, $key, $value)
    {
        if (is_object($collection) && !$collection instanceof ArrayAccess) {
            return $collection->$key = $value;
        }

        return $collection[$key] = $value;
    }

    /**
     * @param array|ArrayAccess $collection
     * @param mixed $key
     */
    public static function unsetValue(&$collection, $key)
    {
        if (is_object($collection) && !$collection instanceof ArrayAccess) {
            unset($collection->$key);
        } else {
            unset($collection[$key]);
        }
    }

    /**
     * @param array|ArrayAccess $collection
     *
     * @throws JSONPathException
     *
     * @return array|ArrayAccess
     */
    public static function arrayValues($collection)
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
