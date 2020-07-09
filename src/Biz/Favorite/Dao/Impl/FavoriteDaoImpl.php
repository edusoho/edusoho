<?php

namespace Biz\Favorite\Dao\Impl;

use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Favorite\Dao\FavoriteDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements FavoriteDao
{
    protected $table = 'favorite';

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'targetType' => $targetType, 'targetId' => $targetId]);
    }

    public function deleteByTargetTypeAndsTargetId($targetType, $targetId)
    {
        return $this->db()->delete($this->table, ['targetType' => $targetType, 'targetId' => $targetId]);
    }

    /*
    * 2017/3/1 为移动端提供服务，其他慎用
    */
    public function findCourseFavoritesNotInClassroomByUserId($userId, $start, $limit)
    {
        $sql = "SELECT f.* FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ?';
        $sql .= "AND f.targetId = c.id AND c.parentId = 0 AND f.targetType = 'course'";
        $sql .= ' ORDER BY createdTime DESC';
        $sql = $this->sql($sql, [], $start, $limit);

        return $this->db()->fetchAll($sql, [$userId]);
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function findUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType, $start, $limit)
    {
        $sql = 'select id from '.CourseDao::TABLE_NAME." where courseSetId in (SELECT c.id FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ? AND c.type = ?';
        $sql .= "AND f.targetId = c.id AND c.parentId = 0 AND f.targetType = 'course')";
        $sql .= ' ORDER BY createdTime DESC';
        $sql = $this->sql($sql, [], $start, $limit);

        return $this->db()->fetchAll($sql, [$userId, $courseType]);
    }

    /*
     * 2017/3/1 为移动端提供服务，其他慎用
     */
    public function countUserFavoriteCoursesNotInClassroomWithCourseType($userId, $courseType)
    {
        $sql = 'select count(*) from '.CourseDao::TABLE_NAME." where courseSetId in (SELECT (c.id) FROM {$this->table} f ";
        $sql .= ' JOIN  '.CourseSetDao::TABLE_NAME.' AS c ON f.userId = ? AND c.type = ?';
        $sql .= "AND f.targetId = c.id AND c.parentId = 0 AND f.targetType = 'course')";

        return $this->db()->fetchColumn($sql, [$userId, $courseType]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => [
                'createdTime', 'id',
            ],
            'conditions' => [
                'userId = :userId',
                'targetType = :targetType',
                'targetType IN ( :targetTypes)',
                'targetId = :targetId',
            ],
        ];
    }
}
