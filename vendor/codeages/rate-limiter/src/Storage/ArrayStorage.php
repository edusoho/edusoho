<?php

namespace Codeages\RateLimiter\Storage;

class ArrayStorage implements Storage
{
    protected $data = array();

    public function set($key, $value, $ttl)
    {
        $this->data[$key] = array(
            'key' => $key,
            'value' => $value,
            'deadline' => time() + $ttl,
        );

        return true;
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        $record = $this->data[$key];
        if ($record['deadline'] < time()) {
            return false;
        }

        return $record['value'];
    }

    public function del($key)
    {
        unset($this->data[$key]);
    }
}
