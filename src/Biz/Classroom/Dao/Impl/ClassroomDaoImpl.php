<?php

namespace Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\ClassroomDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomDaoImpl extends GeneralDaoImpl implements ClassroomDao
{
    protected $table = 'classroom';

    public function getByTitle($title)
    {
        $sql = "SELECT * FROM {$this->table} where title=? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($title));
    }

    public function findByLikeTitle($title)
    {
        if (empty($title)) {
            return array();
        }

        $sql = "SELECT * FROM {$this->table} WHERE `title` LIKE ?; ";

        return $this->db()->fetchAll($sql, array('%'.$title.'%'));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'serializes' => array('assistantIds' => 'json', 'teacherIds' => 'json', 'service' => 'json'),
            'orderbys' => array('name', 'createdTime', 'recommendedSeq', 'studentNum', 'id', 'updatedTime', 'recommendedTime', 'hitNum'),
            'conditions' => array(
                'title = :title',
                'status = :status',
                'title like :titleLike',
                'price > :price_GT',
                'price >= :price_GE',
                'price = :price',
                'private = :private',
                'categoryId IN (:categoryIds)',
                'categoryId =:categoryId',
                'id IN (:classroomIds)',
                'recommended = :recommended',
                'showable = :showable',
                'buyable = :buyable',
                'vipLevelId >= :vipLevelIdGreaterThan',
                'vipLevelId = :vipLevelId',
                'vipLevelId IN ( :vipLevelIds )',
                'orgCode = :orgCode',
                'orgCode PRE_LIKE :likeOrgCode',
                'headTeacherId = :headTeacherId',
                'updatedTime >= :updatedTime_GE',
            ),
        );
    }
}
