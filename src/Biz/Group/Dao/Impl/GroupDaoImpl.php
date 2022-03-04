<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\GroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GroupDaoImpl extends GeneralDaoImpl implements GroupDao
{
    protected $table = '`groups`';

    public function findByTitle($title)
    {
        return $this->findByFields(['title' => $title]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function deleteByUserId($userId)
    {
        return $this->db()->delete($this->table(), ['ownerId' => $userId]);
    }

    public function createQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = '%'.$conditions['title'].'%';
        }

        return parent::createQueryBuilder($conditions);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => ['tagIds' => 'json'],
            'orderbys' => ['createdTime', 'memberNum', 'recommended', 'recommendedSeq', 'recommendedTime'],
            'conditions' => [
                'ownerId=:ownerId',
                'status = :status',
                'title like :title',
                'recommended = :recommended',
                'id NOT IN (:excludeIds)',
            ],
        ];
    }
}
