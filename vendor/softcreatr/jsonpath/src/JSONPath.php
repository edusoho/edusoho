<?php

/**
 * JSONPath implementation for PHP.
 *
 * @license https://github.com/SoftCreatR/JSONPath/blob/main/LICENSE  MIT License
 */

declare(strict_types=1);

namespace Flow\JSONPath;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

use function array_merge;
use function count;
use function crc32;
use function current;
use function end;
use function key;
use function next;
use function reset;

use const E_USER_DEPRECATED;

class JSONPath implements ArrayAccess, Iterator, JsonSerializable, Countable
{
    public const ALLOW_MAGIC = true;

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
     */
    final public function __construct($data = [], bool $options = false)
    {
        $this->data = $data;
        $this->options = $options;
    }

    /**
     * Evaluate an expression
     *
     * @throws JSONPathException
     *
     * @return static
     */
    public function find(string $expression): self
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
                $collectionData = array_merge(...$filteredDataList);
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

        $value = $this->data[$keys[0]] ?? null;

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
     * @throws JSONPathException
     */
    public function parseTokens(string $expression): array
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

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @deprecated Please use getData() instead
     */
    public function data(): array
    {
        trigger_error(
            'Calling JSONPath::data() is deprecated, please use JSONPath::getData() instead.',
            E_USER_DEPRECATED
        );

        return $this->getData();
    }

    /**
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
    public function offsetExists($offset): bool
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
    public function offsetSet($offset, $value): void
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
    public function offsetUnset($offset): void
    {
        AccessHelper::unsetValue($this->data, $offset);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
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
    public function next(): void
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
    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }
}
