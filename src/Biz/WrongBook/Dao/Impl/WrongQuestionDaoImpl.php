<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_wrong_question';

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'answer_scene_id = :answer_scene_id',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time'],
        ];
    }
}
