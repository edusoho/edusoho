<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_wrong_question';

    protected $collectTable = 'biz_wrong_question_collect';

    const WRONG_QUESTION_ORDER_BY = ['submit_time'];

    const  WRONG_QUESTION_COLLECT_ORDER_BY = ['wrong_times'];

    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id")
            ->select("{$this->table}.*, c.wrong_times as wrong_times")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        if (!empty($conditions['pool_id'])) {
            $builder->andWhere('c.pool_id = :pool_id');
        }

        foreach ($orderBys ?: [] as $order => $sort) {
            if (in_array($order, self::WRONG_QUESTION_ORDER_BY)) {
                $builder->addOrderBy($this->table.'.'.$order, $sort);
            }
            if (in_array($order, self::WRONG_QUESTION_COLLECT_ORDER_BY)) {
                $builder->addOrderBy('c.'.$order, $sort);
            }
        }

        return $builder->execute()->fetchAll() ?: [];
    }

    public function countWrongQuestionWithCollect($conditions)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->leftJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id")
            ->select('COUNT(*)');
        if (!empty($conditions['pool_id'])) {
            $builder->andWhere('c.pool_id = :pool_id');
        }

        return (int) $builder->execute()->fetchColumn(0);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'answer_scene_id IN (:answer_scene_ids)',
                'collect_id IN (:collect_ids)',
                'answer_scene_id = :answer_scene_id',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time', 'submit_time'],
        ];
    }
}
