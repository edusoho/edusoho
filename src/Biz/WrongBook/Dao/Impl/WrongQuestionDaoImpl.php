<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionDaoImpl extends AdvancedDaoImpl implements WrongQuestionDao
{
    protected $table = 'biz_item_wrong_question';

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'target_type = : target_type',
                'target_id = : target_id',
                'source = : source',
                'createdTime = :createdTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
