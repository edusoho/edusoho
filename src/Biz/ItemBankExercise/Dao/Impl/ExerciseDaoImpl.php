<?php

namespace Biz\ItemBankExercise\Dao\Impl;

use AppBundle\Common\TimeMachine;
use Biz\ItemBankExercise\Dao\ExerciseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ExerciseDaoImpl extends AdvancedDaoImpl implements ExerciseDao
{
    protected $table = 'item_bank_exercise';

    public function getByQuestionBankId($questionBankId)
    {
        return $this->getByFields(['questionBankId' => $questionBankId]);
    }

    public function findByQuestionBankIds($questionBankIds)
    {
        return $this->findInField('questionBankId', $questionBankIds);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function searchOrderByStudentNumAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $memberTable = $this->getExerciseMemberDao()->table();
        if ($lastDays <= 0) {
            $timeRange = [
                'startTime' => 0,
                'endTime' => PHP_INT_MAX,
            ];
        } else {
            $timeRange = TimeMachine::getTimeRangeByDays($lastDays);
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$memberTable}.studentNumCount, {$this->table}.*")
            ->leftJoin(
                $this->table,
                "(
                    SELECT COUNT(`id`) AS studentNumCount, exerciseId 
                    FROM `{$memberTable}` 
                    WHERE `role` = 'student' 
                        AND createdTime >= {$timeRange['startTime']} 
                        AND createdTime <= {$timeRange['endTime']} 
                    GROUP BY exerciseId
                )",
                $memberTable,
                "{$this->table}.id = {$memberTable}.exerciseId"
            )
            ->orderBy($memberTable.'.studentNumCount', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $itemBankExercises = $builder->execute()->fetchAll() ?: [];

        return $itemBankExercises;
    }

    public function searchOrderByRatingAndLastDays($conditions, $lastDays, $start, $limit)
    {
        $reviceTable = $this->getReviewDao()->table();
        if ($lastDays <= 0) {
            $timeRange = [
                'startTime' => 0,
                'endTime' => PHP_INT_MAX,
            ];
        } else {
            $timeRange = TimeMachine::getTimeRangeByDays($lastDays);
        }

        $builder = $this->createQueryBuilder($conditions)
            ->select("{$reviceTable}.rating_avg, {$this->table}.*")
            ->leftJoin(
                $this->table,
                "(
                    SELECT AVG(`rating`) AS rating_avg, targetId AS exerciseId 
                    FROM `{$reviceTable}` 
                    WHERE parentId = 0
                        AND createdTime >= {$timeRange['startTime']} 
                        AND createdTime <= {$timeRange['endTime']}
                        AND targetType = 'item_bank_exercise'
                    GROUP BY exerciseId
                )",
                $reviceTable,
                "{$this->table}.id = {$reviceTable}.exerciseId"
            )
            ->orderBy($reviceTable.'.rating_avg', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $itemBankExercises = $builder->execute()->fetchAll() ?: [];

        return $itemBankExercises;
    }

    public function findByLikeTitle($title)
    {
        if (empty($title)) {
            return [];
        }

        $sql = "SELECT * FROM {$this->table} WHERE `title` LIKE ?; ";

        return $this->db()->fetchAll($sql, ['%'.$title.'%']);
    }

    protected function getExerciseMemberDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseMemberDao');
    }

    protected function getReviewDao()
    {
        return $this->biz->dao('Review:ReviewDao');
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime', 'seq', 'studentNum', 'rating', 'id', 'recommendedTime', 'recommended', 'recommendedSeq'],
            'serializes' => [
                'teacherIds' => 'delimiter',
                'cover' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'id NOT IN (:excludeIds)',
                'questionBankId = :questionBankId',
                'categoryId in (:categoryIds)',
                'creator = :creator',
                'title like :title',
                'status = :status',
                'status != :excludeStatus',
                'price = :price',
                'studentNum = :studentNum',
                'categoryId = :categoryId',
                'recommended = :recommended',
                'price > :price_GT',
                'questionBankId NOT IN (:excludeQuestionBankIds)',
            ],
        ];
    }
}
