<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\TokenDao;

class TokenDaoImpl extends BaseDao implements TokenDao
{
    protected $table = 'user_token';

    public $serializeFields = array(
        'data' => 'phpserialize'
    );

    public function getToken($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql   = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $token = $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
            return $token ? $that->createSerializer()->unserialize($token, $that->serializeFields) : null;
        });
    }

    public function getTokenByToken($token)
    {
        $that = $this;

        return $this->fetchCached("token:{$token}", $token, function ($token) use ($that) {
            $sql   = "SELECT * FROM {$that->getTable()} WHERE token = ? LIMIT 1";
            $token = $that->getConnection()->fetchAssoc($sql, array($token));
            return $token ? $that->createSerializer()->unserialize($token, $that->serializeFields) : null;
        });
    }

    public function addToken(array $token)
    {
        $token    = $this->createSerializer()->serialize($token, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $token);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert token error.');
        }

        $token = $this->getToken($this->getConnection()->lastInsertId());
        $this->flushCache($token);
        return $token;
    }

    protected function flushCache($token)
    {
        $this->incrVersions(array(
            "{$this->table}:version:userId:{$token['userId']}",
            "{$this->table}:version:type:{$token['type']}"
        ));

        $this->deleteCache(array(
            "id:{$token['id']}",
            "token:{$token['token']}"
        ));
    }

    public function findTokensByUserIdAndType($userId, $type)
    {
        $that = $this;

        $versionKey = "{$this->table}:version:userId:{$userId}";
        $version    = $this->getCacheVersion($versionKey);

        return $this->fetchCached("userId:{$userId}:version:{$version}:type:{$type}", $userId, $type, function ($userId, $type) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE userId = ? and type = ?";
            return $that->getConnection()->fetchAll($sql, array($userId, $type)) ?: null;
        });
    }

    public function getTokenByType($type)
    {
        $that = $this;

        $versionKey = "{$this->table}:version:type:{$type}";
        $version    = $this->getCacheVersion($versionKey);

        return $this->fetchCached("type:{$type}:version:{$version}", $type, function ($type) use ($that) {
            $sql   = "SELECT * FROM {$that->getTable()} WHERE type = ?  and expiredTime > ? order  by createdTime DESC  LIMIT 1";
            $token = $that->getConnection()->fetchAssoc($sql, array($type, time())) ?: null;
            return $token ? $that->createSerializer()->unserialize($token, $that->serializeFields) : null;
        });
    }

    public function deleteToken($id)
    {
        $token  = $this->getToken($id);
        $result = $this->getConnection()->delete($this->table, array('id' => $id));

        $this->flushCache($token);
        return $result;
    }

    public function deleteTokensByExpiredTime($expiredTime, $limit)
    {
        $sql    = "DELETE FROM {$this->table} WHERE expiredTime < ? LIMIT {$limit} ";
        $result = $this->getConnection()->executeQuery($sql, array($expiredTime));
        $this->clearCached();
        return $result;
    }

    public function waveRemainedTimes($id, $diff)
    {
        $sql    = "UPDATE {$this->table} SET remainedTimes = remainedTimes + ? WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->executeQuery($sql, array($diff, $id));

        $sql   = "SELECT * FROM {$this->getTable()} WHERE id = ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;

        $this->flushCache($token);

        return $result;
    }

    public function searchTokenCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user_token')
            ->andWhere('type = :type');

        return $builder;
    }
}
