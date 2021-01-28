<?php

namespace Codeages\Plumber;

use Codeages\RateLimiter\Storage\Storage;
use Swoole\Table;

class SwooleTableRateLimiterStorage implements Storage
{
    private $table;

    public function __construct()
    {
        $this->table = $table = new Table(1024);
        $table->column('data', Table::TYPE_STRING, 32);
        $table->column('deadline', Table::TYPE_INT, 0);
        $table->create();
    }

    public function get($key)
    {
        $row = $this->table->get($key);
        if (!$row) {
            return false;
        }

        if ($row['deadline'] < time()) {
            return false;
        }

        return $row['data'];
    }

    public function set($key, $value, $ttl)
    {
        $this->table->set($key, [
            'data' => $value,
            'deadline' => time() + $ttl,
        ]);
    }

    public function del($key)
    {
        $this->table->del($key);
    }
}
