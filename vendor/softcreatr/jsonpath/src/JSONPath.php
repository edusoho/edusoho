<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

namespace Flow\JSONPath;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class JSONPath implements ArrayAccess, Iterator, JsonSerializable, Countable
{
    const ALLOW_MAGIC = true;

    /**
     * @var array
     */
    protected static $tokenCache = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $options = false;

    /**
     * @param array|ArrayAccess $data
     * @param bool $options
     */
    final public function __construct($data = [], $options = false)
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Evaluate an expression
     *
     * @param string $expression
     *
     * @throws JSONPathException
     *
     * @return static
     */
    public function find($expression)
    {
        $tokens = $this->parseTokens($expression);
        $collectionData = [$this->data];

        foreach ($tokens as $token) {
            /** @var JSONPathToken $token */
            $filter = $token->buildFilter($this->options);
            $filteredDataList = [];

            foreach ($collectionData as $value) {
                if (AccessHelper::isCollectionType($value)) {
                    $filteredDataList[] = $filter->filter($value);
                }
            }

            if (!empty($filteredDataList)) {
                $collectionData = call_user_func_array('array_merge', $filteredDataList);
            } else {
                $collectionData = [];
            }
        }

        return new static($collectionData, $this->options);
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys)) {
            return null;
        }

        $value = isset($this->data[$keys[0]]) ? $this->data[$keys[0]] : null;

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * Evaluate an expression and return the last result
     *
     * @return mixed|null
     */
    public function last()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys)) {
            return null;
        }

        $value = $this->data[end($keys)] ?: null;

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * Evaluate an expression and return the first key
     *
     * @return mixed|null
     */
    public function firstKey()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys)) {
            return null;
        }

        return $keys[0];
    }

    /**
     * Evaluate an expression and return the last key
     *
     * @return mixed|null
     */
    public function lastKey()
    {
        $keys = AccessHelper::collectionKeys($this->data);

        if (empty($keys) || end($keys) === false) {
            return null;
        }

        return end($keys);
    }

    /**
     * @param $expression
     *
     * @throws JSONPathException
     *
     * @return array|mixed
     */
    public function parseTokens($expression)
    {
        $cacheKey = crc32($expression);

        if (isset(static::$tokenCache[$cacheKey])) {
            return static::$tokenCache[$cacheKey];
        }

        $lexer = new JSONPathLexer($expression);
        $tokens = $lexer->parseExpression();

        static::$tokenCache[$cacheKey] = $tokens;

        return $tokens;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @deprecated Please use getData() instead
     */
    public function data()
    {
        trigger_error(
            'Calling JSONPath::data() is deprecated, please use JSONPath::getData() instead.',
            E_USER_DEPRECATED
        );

        return $this->getData();
    }

    /**
     * @param $key
     *
     * @return mixed|null
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return AccessHelper::keyExists($this->data, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        $value = AccessHelper::getValue($this->data, $offset);

        return AccessHelper::isCollectionType($value)
            ? new static($value, $this->options)
            : $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            AccessHelper::setValue($this->data, $offset, $value);
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        AccessHelper::unsetValue($this->data, $offset);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->getData();
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $value = current($this->data);

        return AccessHelper::isCollectionType($value) ? new static($value, $this->options) : $value;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->data);
    }
}
