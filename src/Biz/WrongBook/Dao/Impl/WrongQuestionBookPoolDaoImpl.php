<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionBookPoolDaoImpl extends AdvancedDaoImpl implements WrongQuestionBookPoolDao
{
    public function getPool($user_id, $target_type, $target_id)
    {
        return $this->getByFields(['user_id' => $user_id, 'target_type' => $target_type, 'target_id' => $target_id]);
    }

    protected $table = 'biz_wrong_question_book_pool';

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'user_id = : user_id',
                'target_type = : target_type',
                'target_id = : target_id',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time'],
        ];
    }
}
