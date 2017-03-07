<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\FavoriteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements FavoriteDao
{
    protected $table = 'course_favorite';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('replayId', 'createdTime'),
            'conditions' => array(
                'courseId = :courseId',
                'userId = :userId',
                'type = :type',
                'createdTime >= :createdTime_GE',
                'courseSetId = :courseSetId',
                'courseSetId IN ( :courseSetIds )',
                'courseId NOT IN ( :excludeCourseIds )',
            ),
        );
    }

    public function getByUserIdAndCourseId($userId, $courseId, $type = 'course')
    {
        return $this->getByFields(array('userId' => $userId, 'courseId' => $courseId, 'type' => $type));
    }

    public function searchByUserId($userId, $start, $limit)
    {
        return $this->search(
            array(
                'userId' => $userId,
            ),
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );
    }

    public function getByUserIdAndCourseSetId($userId, $courseSetId, $type = 'course')
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'courseSetId' => $courseSetId,
            'type' => $type,
        ));
    }

    public function countByUserId($userId)
    {
        return $this->count(array(
            'userId' => $userId,
        ));
    }

    public function deleteByCourseId($courseId)
    {
        return $this->db()->delete($this->table(), array('courseId' => $courseId));
    }

    public function findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit)
    {
        $sql = "SELECT f.* FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseDao::TABLENAME.' AS c ON f.userId = ?';
        $sql .= "AND f.courseId = c.id AND c.parentId = 0 AND f.type = 'course'";
        $sql .= " ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        return $this->db()->fetchAll($sql, array($userId));
    }

}
