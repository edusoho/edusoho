<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassDaoImpl extends AdvancedDaoImpl implements MultiClassDao
{
    protected $table = 'multi_class';

    const COURSE_ORDER_BY = ['price', 'studentNum'];

    const MULTI_CLASS_ORDER_BY = ['createdTime'];

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table};";

        return $this->db()->fetchAll($sql, []) ?: [];
    }

    public function findByProductIds(array $productIds)
    {
        return $this->findInField('productId', array_values($productIds));
    }

    public function findByCourseIds(array $courseIds)
    {
        return $this->findInField('courseId', array_values($courseIds));
    }

    public function findByProductId($productId)
    {
        return $this->findByFields(['productId' => $productId]);
    }

    public function findByCreator($creator)
    {
        return $this->findByFields(['creator' => $creator]);
    }

    public function getByTitle($title)
    {
        return $this->getByFields(['title' => $title]);
    }

    public function getByCourseId($courseId)
    {
        return $this->getByFields(['courseId' => $courseId]);
    }

    public function searchMultiClassJoinCourse($conditions, $orderBys, $start, $limit)
    {
        $courseTable = 'course_v8';
        $multiClassTable = 'multi_class';
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin($multiClassTable, $courseTable, '', "{$courseTable}.id = {$multiClassTable}.courseId")
            ->select("{$multiClassTable}.*, {$courseTable}.price as price, {$courseTable}.studentNum as studentNum")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys ?: [] as $order => $sort) {
            if (in_array($order, self::COURSE_ORDER_BY)) {
                $builder->addOrderBy($courseTable.'.'.$order, $sort);
            }
            if (in_array($order, self::MULTI_CLASS_ORDER_BY)) {
                $builder->addOrderBy($multiClassTable.'.'.$order, $sort);
            }
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime', 'startTime', 'endTime'],
            'conditions' => [
                'id = :id',
                'multi_class.id IN (:ids)',
                'productId = :productId',
                'productId IN ( :productIds)',
                'courseId IN ( :courseIds)',
                'copyId = :copyId',
                'multi_class.title LIKE :titleLike',
            ],
        ];
    }
}
