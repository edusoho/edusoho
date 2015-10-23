<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomReviewDao;

class ClassroomReviewDaoImpl extends BaseDao implements ClassroomReviewDao
{

    protected $table = 'classroom_review';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function getReview($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getReviewRatingSumByClassroomId($classroomId)
    {
        $sql = "SELECT sum(rating) FROM {$this->table} WHERE classroomId = ?";

        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function getReviewCountByClassroomId($classroomId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE classroomId = ?";

        return $this->getConnection()->fetchColumn($sql, array($classroomId));
    }

    public function searchReviews($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchReviewCount($conditions)
    {
        $builder = $this->_createSearchBuilder($conditions)
                         ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function getReviewByUserIdAndClassroomId($userId, $classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND userId = ? LIMIT 1;";

        return $this->getConnection()->fetchAssoc($sql, array($classroomId, $userId)) ?: null;
    }

    public function addReview($review)
    {
        $affected = $this->getConnection()->insert($this->table, $review);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert review error.');
        }

        return $this->getReview($this->getConnection()->lastInsertId());
    }

    public function updateReview($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getReview($id);
    }

    public function deleteReview($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    private function _createSearchBuilder($conditions)
    {
        if (isset($conditions['content'])) {
            $conditions['content'] = "%{$conditions['content']}%";
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('userId = :userId')
            ->andWhere('classroomId = :classroomId')
            ->andWhere('rating = :rating')
            ->andWhere('content LIKE :content')
            ->andWhere('classroomId IN (:classroomIds)');

        return $builder;
    }
}
