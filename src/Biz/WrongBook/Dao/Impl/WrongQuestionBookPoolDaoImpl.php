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
