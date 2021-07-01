<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionBookPoolDaoImpl extends AdvancedDaoImpl implements WrongQuestionBookPoolDao
{
    protected $table = 'biz_wrong_question_book_pool';

    public function getPoolByUserIdAndTargetTypeAndTargetId($userId, $targetType, $targetId)
    {
        return $this->getByFields(['user_id' => $userId, 'target_type' => $targetType, 'target_id' => $targetId]);
    }

    public function getPoolByFieldsGroupByTargetType($fields)
    {
        $builder = $this->createQueryBuilder($fields)
            ->select('sum(`item_num`) as sum_wrong_num,user_id,target_type')
            ->groupBy('target_type');

        return $builder->execute()->fetchAll();
    }

    public function getWrongBookPoolByFields($fields)
    {
        $builder = $this->createQueryBuilder($fields)
            ->select('*');

        return $builder->execute()->fetchAll();
    }

    public function searchPoolByConditions($conditions, $orderBys, $start, $limit)
    {
        $table = $this->getTableName($conditions);
        $conditions['keyWord'] = isset($conditions['keyWord']) ? $conditions['keyWord'] : '';
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin('biz_wrong_question_book_pool', $table, 't', 't.id = biz_wrong_question_book_pool.target_id')
            ->select('biz_wrong_question_book_pool.*')
            ->andWhere('title like :keyWord')
            ->orderBy('biz_wrong_question_book_pool.updated_time','DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: [];
    }

    public function countPoolByConditions($conditions)
    {
        $table = $this->getTableName($conditions);
        $conditions['keyWord'] = isset($conditions['keyWord']) ? $conditions['keyWord'] : '';
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin('biz_wrong_question_book_pool', $table, 't', 't.id = biz_wrong_question_book_pool.target_id')
            ->select('COUNT(*)')
            ->andWhere('title like :keyWord');

        return $builder->execute()->fetchColumn(0);
    }

    protected function getTableName($conditions)
    {
        if ('classroom' == $conditions['target_type']) {
            $table = 'classroom';
        } elseif ('exercise' == $conditions['target_type']) {
            $table = 'item_bank_exercise';
        } else {
            $table = 'course_set_v8';
        }

        return  $table;
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'user_id = :user_id',
                'target_type = :target_type',
                'target_id = :target_id',
                'createdTime = :createdTime',
            ],
            'orderbys' => ['id', 'created_time'],
        ];
    }
}
