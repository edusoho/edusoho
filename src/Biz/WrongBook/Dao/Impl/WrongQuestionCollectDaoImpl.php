<?php

namespace Biz\WrongBook\Dao\Impl;

use Biz\WrongBook\Dao\WrongQuestionCollectDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class WrongQuestionCollectDaoImpl extends AdvancedDaoImpl implements WrongQuestionCollectDao
{
    public function getCollect($pool_id, $item_id)
    {
        return $this->getByFields(['pool_id' => $pool_id, 'item_id' => $item_id]);
    }

    protected $table = 'biz_wrong_question_collect';

    public function declares()
    {
        return [
            'timestamps' => ['created_time', 'updated_time'],
            'conditions' => [
                'id = :id',
                'pool_id = :pool_id',
                'item_id = :item_id',
                'created_time = :created_time',
            ],
            'orderbys' => ['id', 'created_time'],
        ];
    }
}
