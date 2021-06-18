<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_wrong_question';

    protected $collectTable = 'biz_wrong_question_collect';

    public function searchWrongQuestionWithCollect($conditions, $orderBys, $start, $limit, $columns)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("{$this->table}.*, c.wrong_times as wrong_times")
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: [];
    }

    protected function createQueryBuilder($conditions)
    {
        $builder = parent::createQueryBuilder($conditions);
        $builder->leftJoin($this->table, $this->collectTable, 'c', "c.id = {$this->table}.collect_id");

        if (!empty($conditions['pool_id'])) {
            $builder->andWhere('c.pool_id = :pool_id');
        }

        return $builder;
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'collect_id IN (:collect_ids)',
                'answer_scene_id = :answer_scene_id',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time', 'submit_time'],
        ];
    }
}
