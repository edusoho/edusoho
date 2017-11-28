<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\FavoriteDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Dao\CourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements FavoriteDao
{
    protected $table = 'course_favorite';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('replayId', 'createdTime', 'id'),
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

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit)
    {
        $sql = "SELECT f.* FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ?';
        $sql .= "AND f.courseSetId = c.id AND c.parentId = 0 AND f.type = 'course'";
        $sql .= ' ORDER BY createdTime DESC';
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($userId));
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType, $start, $limit)
    {
        $sql = 'select id from '.CourseDao::TABLE_NAME." where courseSetId in (SELECT c.id FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ? AND c.type = ?';
        $sql .= "AND f.courseSetId = c.id AND c.parentId = 0 AND f.type = 'course')";
        $sql .= ' ORDER BY createdTime DESC';
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array($userId, $courseType));
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function countUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType)
    {
        $sql = 'select count(*) from '.CourseDao::TABLE_NAME." where courseSetId in (SELECT (c.id) FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ? AND c.type = ?';
        $sql .= "AND f.courseSetId = c.id AND c.parentId = 0 AND f.type = 'course')";

        return $this->db()->fetchColumn($sql, array($userId, $courseType));
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
}
