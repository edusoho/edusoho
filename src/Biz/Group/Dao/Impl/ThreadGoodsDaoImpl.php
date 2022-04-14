<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\ThreadGoodsDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadGoodsDaoImpl extends GeneralDaoImpl implements ThreadGoodsDao
{
    protected $table = 'groups_thread_goods';

    public function deleteByThreadIdAndType($id, $type)
    {
        $sql = "DELETE FROM {$this->table} WHERE threadId = ? AND type = ? ";

        return $this->db()->executeUpdate($sql, [$id, $type]);
    }

    public function deleteByThreadIds(array $threadIds)
    {
        if (empty($threadIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($threadIds) - 1).'?';
        $sql = "DELETE FROM {$this->table} WHERE `threadId` IN ({$marks});";

        return $this->db()->executeUpdate($sql, $threadIds);
    }

    public function sumGoodsCoins($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(coin)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return [
            'serializes' => [
                'tagIds' => 'json',
            ],
            'orderbys' => ['name', 'createdTime', 'id'],
            'conditions' => [
                'threadId = :threadId',
                'fileId = :fileId',
                'postId = :postId',
                'type = :type',
            ],
        ];
    }
}
