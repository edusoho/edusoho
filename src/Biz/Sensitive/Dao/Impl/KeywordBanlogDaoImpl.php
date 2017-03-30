<?php

namespace Biz\Sensitive\Dao\Impl;

use Biz\Sensitive\Dao\KeywordBanlogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class KeywordBanlogDaoImpl extends GeneralDaoImpl implements KeywordBanlogDao
{
    protected $table = 'keyword_banlog';

    public function searchBanlogsByUserIds($userIds, $orderBy, $start, $limit)
    {
        if (empty($userIds)) {
            return array();
        }
        // @TODO  SQL Inject
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId IN ({$marks}) ORDER BY id DESC LIMIT {$start}, {$limit};";

        return $this->db()->fetchAll($sql, $userIds);
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime', 'id',
        );

        $declares['conditions'] = array(
            'id = :id',
            'userId = :userId',
            'state = :state',
            'keywordId = :keywordId',
            'UPPER(keywordName) LIKE :keywordName',
        );

        return $declares;
    }

    protected function createQueryBuilder($conditions)
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

        return parent::createQueryBuilder($conditions);
    }
}
