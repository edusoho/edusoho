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

        return $this->db()->executeUpdate($sql, array($id, $type));
    }

    public function sumGoodsCoins($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(coin)');

        return $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'tagIds' => 'json',
            ),
            'orderbys' => array('name', 'createdTime', 'id'),
            'conditions' => array(
                'threadId = :threadId',
                'fileId = :fileId',
                'postId = :postId',
                'type = :type',
            ),
        );
    }
}
