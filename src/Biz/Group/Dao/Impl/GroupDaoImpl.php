<?php

namespace Biz\Group\Dao\Impl;

use Biz\Group\Dao\GroupDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class GroupDaoImpl extends GeneralDaoImpl implements GroupDao
{
    protected $table = '`groups`';

    public function findByTitle($title)
    {
        return $this->findByFields(array('title' => $title));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
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
        return array(
            'timestamps' => array('createdTime'),
            'serializes' => array('tagIds' => 'json'),
            'orderbys' => array('createdTime', 'memberNum'),
            'conditions' => array(
                'ownerId=:ownerId',
                'status = :status',
                'title like :title',
            ),
        );
    }
}
