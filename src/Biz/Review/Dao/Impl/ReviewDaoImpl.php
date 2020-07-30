<?php

namespace Biz\Review\Dao\Impl;

use Biz\Review\Dao\ReviewDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ReviewDaoImpl extends GeneralDaoImpl implements ReviewDao
{
    protected $table = 'review';

    public function declares()
    {
        return [
            'serializes' => ['meta' => 'json'],
            'orderbys' => ['createdTime', 'id', 'updatedTime', 'rating'],
            'conditions' => [
                'targetType = :targetType',
                'targetId = :targetId',
                'userId = :userId',
                'parentId = :parentId',
                'targetId IN (:targetIds)',
                'userId IN (:userIds)',
                'content LIKE :content',
                'rating = :rating',
                'targetType IN (:targetTypes)',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
        ];
    }

    public function getByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getByFields(['userId' => $userId, 'targetType' => $targetType, 'targetId' => $targetId, 'parentId' => 0]);
    }

    public function sumRatingByConditions($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(rating)');

        return $builder->execute()->fetchColumn(0);
    }

    public function deleteByParentId($parentId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE parentId = ?";

        return $this->db()->executeQuery($sql, [$parentId]);
    }

    public function deleteByTargetTypeAndTargetId($targetType, $targetId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE targetType = ? AND targetId = ?";

        return $this->db()->executeQuery($sql, [$targetType, $targetId]);
    }

//    TODO: 暂时兼容后台评价管理列表，后续应删除 ---------- 开始
    public function countCourseReviews($conditions)
    {
        $courseSql = "SELECT r.* FROM {$this->table} r WHERE r.targetType = 'course' ";
        $goodsSql = "SELECT r.* FROM {$this->table} r INNER JOIN goods g ON g.id = r.targetId AND g.type = 'course' AND r.targetType = 'goods' ";

        if (!empty($conditions['courseTitle'])) {
            $courseSql = "
                SELECT r.* FROM review r INNER JOIN course_v8 c ON r.targetId = c.id AND c.courseSetTitle LIKE :courseTitleLike AND r.targetType = 'course'
            ";

            $goodsSql = "
                SELECT r.* FROM review r INNER JOIN goods g INNER JOIN product p INNER JOIN course_v8 c 
                ON r.targetId = g.id AND g.productId = p.id AND p.targetId = c.courseSetId AND p.targetType = 'course' 
                AND r.targetType = 'goods' AND c.courseSetTitle LIKE :courseTitleLike
            ";
            $conditions['courseTitleLike'] = "%{$conditions['courseTitle']}%";
            unset($conditions['courseTitle']);
        }

        if (!empty($conditions['userId'])) {
            $courseSql .= ' AND r.userId = :userId ';
            $goodsSql .= ' AND r.userId = :userId ';
        }

        if (!empty($conditions['rating'])) {
            $courseSql .= ' AND r.rating = :rating ';
            $goodsSql .= ' AND r.rating = :rating ';
        }

        if (!empty($conditions['content'])) {
            $courseSql .= ' AND r.content LIKE :content ';
            $goodsSql .= ' AND r.content LIKE :content ';
            $conditions['content'] = "%{$conditions['content']}%";
        }

        if (isset($conditions['parentId'])) {
            $courseSql .= ' AND r.parentId = :parentId ';
            $goodsSql .= ' AND r.parentId = :parentId ';
        }

        $sql = $this->sql("SELECT COUNT(*) FROM ({$courseSql} UNION {$goodsSql}) AS m");

        return $this->db()->fetchColumn($sql, $conditions);
    }

    public function searchCourseReviews($conditions, $orderBys, $start, $limit)
    {
        $courseSql = "SELECT r.* FROM {$this->table} r WHERE r.targetType = 'course' ";
        $goodsSql = "SELECT r.* FROM {$this->table} r INNER JOIN goods g ON g.id = r.targetId AND g.type = 'course' AND r.targetType = 'goods' ";

        if (!empty($conditions['courseTitle'])) {
            $courseSql = "
                SELECT r.* FROM review r INNER JOIN course_v8 c ON r.targetId = c.id AND c.courseSetTitle LIKE :courseTitleLike AND r.targetType = 'course'
            ";

            $goodsSql = "
                SELECT r.* FROM review r INNER JOIN goods g INNER JOIN product p INNER JOIN course_v8 c 
                ON r.targetId = g.id AND g.productId = p.id AND p.targetId = c.courseSetId AND p.targetType = 'course' 
                AND r.targetType = 'goods' AND c.courseSetTitle LIKE :courseTitleLike
            ";
            $conditions['courseTitleLike'] = "%{$conditions['courseTitle']}%";
            unset($conditions['courseTitle']);
        }

        if (!empty($conditions['userId'])) {
            $courseSql .= ' AND r.userId = :userId ';
            $goodsSql .= ' AND r.userId = :userId ';
        }

        if (!empty($conditions['rating'])) {
            $courseSql .= ' AND r.rating = :rating ';
            $goodsSql .= ' AND r.rating = :rating ';
        }

        if (!empty($conditions['content'])) {
            $courseSql .= ' AND r.content LIKE :content ';
            $goodsSql .= ' AND r.content LIKE :content ';
            $conditions['content'] = "%{$conditions['content']}%";
        }

        if (isset($conditions['parentId'])) {
            $courseSql .= ' AND r.parentId = :parentId ';
            $goodsSql .= ' AND r.parentId = :parentId ';
        }

        $sql = $this->sql("{$courseSql} UNION {$goodsSql} ", $orderBys, $start, $limit);

        return $this->db()->fetchAll($sql, $conditions);
    }

    public function countClassroomReviews($conditions)
    {
        $sql = "
            SELECT r.* FROM review r INNER JOIN goods g ON g.id=r.targetId AND g.type='classroom' AND r.targetType='goods'
        ";

        if (!empty($conditions['userId'])) {
            $sql .= 'AND r.userId = :userId ';
        }

        if (!empty($conditions['classroomTitle'])) {
            $sql .= 'AND g.title LIKE :classroomTitle ';
            $conditions['classroomTitle'] = "%{$conditions['classroomTitle']}%";
        }

        if (!empty($conditions['rating'])) {
            $sql .= ' AND r.rating = :rating ';
        }

        if (isset($conditions['parentId'])) {
            $sql .= ' AND r.parentId = :parentId';
        }

        $sql = $this->sql("SELECT COUNT(*) FROM ({$sql}) AS m");

        return $this->db()->fetchColumn($sql, $conditions);
    }

    public function searchClassroomReviews($conditions, $orderBys, $start, $limit)
    {
        $sql = "
            SELECT r.* FROM review r INNER JOIN goods g ON g.id=r.targetId AND g.type='classroom' AND r.targetType='goods'
        ";

        if (!empty($conditions['userId'])) {
            $sql .= 'AND r.userId = :userId ';
        }

        if (!empty($conditions['classroomTitle'])) {
            $sql .= 'AND g.title LIKE :classroomTitle ';
            $conditions['classroomTitle'] = "%{$conditions['classroomTitle']}%";
        }

        if (!empty($conditions['rating'])) {
            $sql .= ' AND r.rating = :rating ';
        }

        if (isset($conditions['parentId'])) {
            $sql .= ' AND r.parentId = :parentId';
        }

        $sql = $this->sql($sql, $orderBys, $start, $limit);

        return $this->db()->fetchAll($sql, $conditions);
    }

    //    TODO: 暂时兼容后台评价管理列表，后续应删除 ---------- 结束
}
