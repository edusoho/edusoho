<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseSetDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseSetDaoImpl extends GeneralDaoImpl implements CourseSetDao
{
    protected $table = 'c2_course_set';

    public function findCourseSetsByParentIdAndLocked($parentId, $locked)
    {
        return $this->findByFields(array('parentId' => $parentId, 'locked' => $locked));
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findLikeTitle($title)
    {
        if (empty($title)) {
            $title = '';
        }
        $title = '%'.$title.'%';
        $sql = "SELECT * FROM {$this->table} WHERE title LIKE ?";

        return $this->db()->fetchAll($sql, array($title));
    }

    public function analysisCourseSetDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(id) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM {$this->table} WHERE createdTime >= ? AND createdTime <= ?
            group by from_unixtime(createdTime,'%Y-%m-%d') order by date ASC";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }

    public function clearSubtitle($id)
    {
        $sql = "UPDATE {$this->table} SET subtitle = '' WHERE id = ?";

        return $this->db()->executeUpdate($sql, array($id));
    }

    protected function _createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value == 0) {
                return true;
            }
            if (empty($value)) {
                return false;
            }

            return true;
        });

        return parent::_createQueryBuilder($conditions);
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'id IN ( :ids )',
                'status = :status',
                'isVip = :isVip',
                'categoryId = :categoryId',
                'title LIKE :title',
                'creator LIKE :creator',
                'type = :type',
                'recommended = :recommended',
                'id NOT IN (:excludeIds)',
                'parentId = :parentId',
                'parentId > :parentId_GT',
                'createdTime >= :startTime',
                'createdTime <= :endTime',
                'minCoursePrice = :minCoursePrice',
                'maxCoursePrice > :maxCoursePrice_GT',
            ),
            'serializes' => array(
                'tags' => 'delimiter',
                'goals' => 'delimiter',
                'audiences' => 'delimiter',
                'cover' => 'json',
            ),
            'orderbys' => array(
                'createdTime',
                'updatedTime',
                'recommendedSeq',
                'hitNum',
                'recommendedTime',
                'rating',
                'studentNum',
            ),
            'timestamps' => array(
                'createdTime', 'updatedTime',
            ),
        );
    }
}
