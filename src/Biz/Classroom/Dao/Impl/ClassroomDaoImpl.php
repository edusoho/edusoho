<?php

namespace Biz\Classroom\Dao\Impl;

use AppBundle\Common\TimeMachine;
use Biz\Classroom\Dao\ClassroomDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ClassroomDaoImpl extends AdvancedDaoImpl implements ClassroomDao
{
    protected $table = 'classroom';

    public function getByTitle($title)
    {
        $sql = "SELECT * FROM {$this->table} where title=? LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$title]);
    }

    public function findProductIdAndGoodsIdsByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT c.id AS classroomId, p.id as productId, g.id as goodsId FROM {$this->table} c 
                LEFT JOIN `product` p ON c.id=p.targetId AND p.targetType = 'classroom'
                LEFT JOIN `goods` g ON g.productId = p.id 
                WHERE c.id IN ({$marks});";

        return $this->db()->fetchAll($sql, $ids);
    }

    public function search($conditions, $orderBy, $start, $limit, $columns = [])
    {
        if (array_key_exists('studentNum', $orderBy) && array_key_exists('lastDays', $conditions) && $conditions['lastDays'] > 0) {
            $timeRange = TimeMachine::getTimeRangeByDays($conditions['lastDays']);

            return $this->searchByStudentNumAndTimeRange($conditions, $timeRange, $orderBy['studentNum'], $start, $limit);
        }

        if (array_key_exists('rating', $orderBy) && array_key_exists('lastDays', $conditions) && $conditions['lastDays'] > 0) {
            $timeRange = TimeMachine::getTimeRangeByDays($conditions['lastDays']);

            return $this->searchByRatingAndTimeRange($conditions, $timeRange, $orderBy['rating'], $start, $limit);
        }

        return parent::search($conditions, $orderBy, $start, $limit, $columns);
    }

    /**
     *  根据一段时间内的加入人数排序
     */
    protected function searchByStudentNumAndTimeRange($conditions, $timeRange, $orderBy = 'DESC', $start, $limit)
    {
        $classroomMemberTable = $this->getClassroomMemberDao()->table();

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$classroomMemberTable}.studentNumCount, {$this->table}.*")
            ->leftJoin(
                $this->table,
                "(
                    SELECT COUNT(`id`) AS studentNumCount, classroomId 
                    FROM `{$classroomMemberTable}` 
                    WHERE `role` LIKE '%|student|%' 
                        AND createdTime >= {$timeRange['startTime']} 
                        AND createdTime <= {$timeRange['endTime']} 
                    GROUP BY classroomId
                )",
                $classroomMemberTable,
                "{$this->table}.id = {$classroomMemberTable}.classroomId"
            )
            ->orderBy($classroomMemberTable.'.studentNumCount', $orderBy)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $classrooms = $builder->execute()->fetchAll() ?: [];
        foreach ($classrooms as &$classroom) {
            $classroom['studentNum'] = empty($classroom['studentNumCount']) ? 0 : $classroom['studentNumCount'];
        }

        return $classrooms;
    }

    /**
     * 根据一段时间内的评价平均分排序
     */
    protected function searchByRatingAndTimeRange($conditions, $timeRange, $orderBy = 'DESC', $start, $limit)
    {
        $classroomReviceTable = $this->getClassroomReviewDao()->table();

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$classroomReviceTable}.rating_avg, {$this->table}.*")
            ->leftJoin(
                $this->table,
                "(
                    SELECT AVG(`rating`) AS rating_avg, classroomId 
                    FROM `{$classroomReviceTable}` 
                    WHERE parentId = 0
                        AND createdTime >= {$timeRange['startTime']} 
                        AND createdTime <= {$timeRange['endTime']} 
                    GROUP BY classroomId
                )",
                $classroomReviceTable,
                "{$this->table}.id = {$classroomReviceTable}.classroomId"
            )
            ->orderBy($classroomReviceTable.'.rating_avg', $orderBy)
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $classrooms = $builder->execute()->fetchAll() ?: [];
        foreach ($classrooms as &$classroom) {
            $classroom['rating'] = empty($classroom['rating_avg']) ? 0 : $classroom['rating_avg'];
        }

        return $classrooms;
    }

    public function findByLikeTitle($title)
    {
        if (empty($title)) {
            return [];
        }

        $sql = "SELECT * FROM {$this->table} WHERE `title` LIKE ?; ";

        return $this->db()->fetchAll($sql, ['%'.$title.'%']);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function refreshHotSeq()
    {
        $sql = "UPDATE {$this->table} set hotSeq = 0;";
        $this->db()->exec($sql);
    }

    /**
     * @return Biz\Classroom\Dao\ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->biz->dao('Classroom:ClassroomMemberDao');
    }

    /**
     * @return Biz\Classroom\Dao\ClassroomReviewDao
     */
    protected function getClassroomReviewDao()
    {
        return $this->biz->dao('Classroom:ClassroomReviewDao');
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['assistantIds' => 'json', 'teacherIds' => 'json', 'service' => 'json'],
            'orderbys' => ['rating', 'name', 'createdTime', 'recommendedSeq', 'studentNum', 'id', 'updatedTime', 'recommendedTime', 'hitNum', 'hotSeq', 'price'],
            'conditions' => [
                'title = :title',
                'status = :status',
                'status != :excludeStatus',
                'title like :titleLike',
                'price > :price_GT',
                'price >= :price_GE',
                'price = :price',
                'private = :private',
                'categoryId IN (:categoryIds)',
                'categoryId =:categoryId',
                'id IN (:classroomIds)',
                'id in (:ids)',
                'recommended = :recommended',
                'showable = :showable',
                'buyable = :buyable',
                'orgCode = :orgCode',
                'orgCode PRE_LIKE :likeOrgCode',
                'headTeacherId = :headTeacherId',
                'updatedTime >= :updatedTime_GE',
                'id NOT IN (:excludeIds)',
                'creator = :creator',
                'creator IN (:creators)',
                'creator = :userId',
            ],
        ];
    }
}
