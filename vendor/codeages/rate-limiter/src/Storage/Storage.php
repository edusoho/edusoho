<?php

namespace Codeages\RateLimiter\Storage;

interface Storage
{
    /**
     * @return bool
     */
    public function set($key, $value, $ttl);

    /**
     * @return bool
     */
    public function get($key);

    /**
     * @return bool
     */
    public function del($key);

    // public function transactional(\Closure $func);
}
