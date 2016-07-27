<?php

namespace SensitiveWord\Service\Sensitive\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use SensitiveWord\Service\Sensitive\Dao\KeywordBanlogDao;

class KeywordBanlogDaoImpl extends BaseDao implements KeywordBanlogDao
{
    protected $table = 'keyword_banlog';

    public function addBanlog(array $fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert keyword banlog error.');
        }
        return $this->getBanlog($this->getConnection()->lastInsertId());
    }

    public function getBanlog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function searchBanlogs($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createBanlogQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchBanlogsByUserIds($userIds, $orderBy, $start, $limit)
    {
        if (empty($userIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE userId IN ({$marks}) ORDER BY id DESC LIMIT {$start}, {$limit};";
        return $this->getConnection()->fetchAll($sql, $userIds);
    }

    public function searchBanlogsCount($conditions)
    {
        $builder = $this->createBanlogQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function createLogQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);
        return $this->createDynamicQueryBuilder($conditions)
            ->andWhere('keywordId = :keywordId');
    }

    protected function createBanlogQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }
            return true;
        });
        if (isset($conditions['keyword'])) {
            if ($conditions['searchBanlog'] == 'id') {
                $conditions['id'] = $conditions['keyword'];
            } elseif ($conditions['searchBanlog'] == 'name') {
                $conditions['keywordName'] = "%{$conditions['keyword']}%";
            }
        }

        return $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'keyword_banlog')
            ->andWhere('id = :id')
            ->andWhere('userId = :userId')
            ->andWhere('state = :state')
            ->andWhere('UPPER(keywordName) LIKE :keywordName');
    }
}
