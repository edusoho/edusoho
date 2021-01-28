<?php

namespace Codeages\Biz\Framework\Dao;

use Symfony\Component\EventDispatcher\Event;

class CacheEvent extends Event
{
    public $key;

    public $value;

    public $lifetime;

    public function __construct($key, $value = null, $lifetime = 0)
    {
        $this->key = $key;
        $this->value = $value;
        $this->lifetime = $lifetime;
    }
}
