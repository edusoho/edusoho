<?php

namespace Codeages\RateLimiter\Storage;

class MySQLPDOStorage implements Storage
{
    protected $pdo;

    protected $table;

    public function __construct(\PDO $pdo, array $options = array())
    {
        if (\PDO::ERRMODE_EXCEPTION !== $pdo->getAttribute(\PDO::ATTR_ERRMODE)) {
            throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))', __CLASS__));
        }
        $this->pdo = $pdo;

        $options = array_replace(array(
            'table' => 'ratelimit',
        ), $options);

        $this->table = $options['table'];
    }

    public function set($key, $value, $ttl)
    {
        $sql = "INSERT INTO {$this->table} (_key, data, deadline) VALUES (:_key, :data, :time) ON DUPLICATE KEY UPDATE data = VALUES(data), deadline = VALUES(deadline)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':_key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $value, \PDO::PARAM_STR);
        $stmt->bindValue(':time', time() + $ttl, \PDO::PARAM_INT);
        $stmt->execute();

        return true;
    }

    public function get($key)
    {
        $sql = "SELECT * FROM {$this->table} WHERE _key = :_key";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':_key', $key, \PDO::PARAM_STR);
        $stmt->execute();

        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($record['deadline'] < time()) {
            return false;
        }

        return $record['data'];
    }

    public function del($key)
    {
        $sql = "DELETE FROM {$this->table} WHERE _key = :_key";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':_key', $key, \PDO::PARAM_STR);
        $stmt->execute();
    }
}
