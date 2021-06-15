<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionBookPoolDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionBookPoolDaoImpl extends AdvancedDaoImpl implements WrongQuestionBookPoolDao
{
    protected $table = 'biz_wrong_question_book_pool';

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'target_type = : target_type',
                'target_id = : target_id',
                'createdTime = :createdTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
