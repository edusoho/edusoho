<?php
namespace Biz\User\Dao\Impl;

use Biz\User\Dao\TokenDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TokenDaoImpl extends GeneralDaoImpl implements TokenDao
{
    protected $table = 'user_token';

    public $serializeFields = array(
        'data' => 'phpserialize' //FIXME 目前DaoProxy中未支持该类型
    );

    public function get($id, $lock = false)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $token = $this->db()->fetchAssoc($sql, array($id)) ?: null;
        // return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
        return $token;
    }

    public function getByToken($token)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        $token = $this->db()->fetchAssoc($sql, array($token));
        // return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
        return $token;
    }

    public function create($token)
    {
        // $token = $this->createSerializer()->serialize($token, $this->serializeFields);
        return parent::create($token);
    }

    public function findByUserIdAndType($userId, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? and type = ?";
        return $this->db()->fetchAll($sql, array($userId, $type)) ?: null;
    }

    public function getByType($type)
    {
        $sql   = "SELECT * FROM {$this->table} WHERE type = ?  and expiredTime > ? order  by createdTime DESC  LIMIT 1";
        $token = $this->db()->fetchAssoc($sql, array($type, time())) ?: null;
        // return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
        return $token;
    }

    public function deleteByExpiredTime($expiredTime, $limit)
    {
        $sql    = "DELETE FROM {$this->table} WHERE expiredTime < ? LIMIT {$limit} ";
        $result = $this->db()->executeQuery($sql, array($expiredTime));
        $this->clearCached();
        return $result;
    }

    public function waveRemainedTimes($id, $diff)
    {
        $sql    = "UPDATE {$this->table} SET remainedTimes = remainedTimes + ? WHERE id = ? LIMIT 1";
        $result = $this->db()->executeQuery($sql, array($diff, $id));

        $sql   = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $token = $this->db()->fetchAssoc($sql, array($id)) ?: null;

        $this->flushCache($token);

        return $result;
    }

    public function declares()
    {
        return array(
            'conditions' => array('type = :type')
        );
    }
}
