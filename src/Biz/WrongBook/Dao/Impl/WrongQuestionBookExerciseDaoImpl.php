<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionBookExerciseDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionBookExerciseDaoImpl extends AdvancedDaoImpl implements WrongQuestionBookExerciseDao
{
    protected $table = 'biz_wrong_book_exercise';

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'serializes' => [
                'regulation' => 'json',
            ],
            'orderbys' => ['id'],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
