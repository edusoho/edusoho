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
            'conditions' => [
                'id = :id',
                'pool_id = :pool_id',
                'item_id = :item_id',
                'createdTime = :createdTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
