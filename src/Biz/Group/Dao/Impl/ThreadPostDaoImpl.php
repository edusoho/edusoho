<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\ThreadPostDao;
use Codeages\Biz\Framework\Dao\DaoException;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThreadPostDaoImpl extends GeneralDaoImpl implements ThreadPostDao
{
    protected $table = 'groups_thread_post';

    public function searchPostsThreadIds($conditions, $orderbys, $start, $limit)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('distinct threadId, id')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $declares = $this->declares();
        foreach ($orderbys ?: array() as $field => $direction) {
            if (!in_array($field, $declares['orderbys'])) {
                throw new DaoException(sprintf("SQL order by field is only allowed '%s', but you give `{$field}`.", implode(',', $declares['orderbys'])));
            }
            if (!in_array(strtoupper($direction), array('ASC', 'DESC'))) {
                throw new DaoException("SQL order by direction is only allowed `ASC`, `DESC`, but you give `{$direction}`.");
            }
            $builder->addOrderBy($field, $direction);
        }

        return $builder->execute()->fetchAll() ?: array();
    }

    public function countPostsThreadIds($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('count(distinct threadId)');

        return $builder->execute()->fetchColumn(0);
    }

    public function deleteByThreadId($threadId)
    {
        return $this->db()->delete($this->table, array('threadId' => $threadId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('tagIds' => 'json'),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id < :id',
                'userId = :userId',
                'postId = :postId',
                'adopt = :adopt',
                'threadId = :threadId',
            ),
        );
    }
}
