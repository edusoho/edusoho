<?php

namespace Codeages\Biz\Framework\Utility;

/**
 * 字符串工具类
 *
 * 这个类的代码Copy自 Laravel Framework 的 Illuminate\Support\Str
 * 参见：https://github.com/laravel/framework/blob/5.5/src/Illuminate/Support/Str.php
 */
class Str
{
    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = array();

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = array();
    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = array();

    /**
     * Convert a value to camel case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Return the length of the given string.
     *
     * @param string $value
     * @param string $encoding
     *
     * @return int
     */
    public static function length($value, $encoding = null)
    {
        if ($encoding) {
            return mb_strlen($value, $encoding);
        }

        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int    $limit
     * @param string $end
     *
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     *
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;
        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;
        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
