<?php

namespace Codeages\Biz\ItemBank\Item\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;

class QuestionDaoImpl extends AdvancedDaoImpl implements QuestionDao
{
    protected $table = 'biz_question';

    public function findByItemId($itemId)
    {
        return $this->findByFields(['item_id' => $itemId]);
    }

    public function findByItemsIds($itemIds)
    {
        return $this->findInField('item_id', $itemIds);
    }

    public function findQuestionsByQuestionIds($questionIds)
    {
        return $this->findInField('id', $questionIds);
    }
    
    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [
                'id',
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'id in (:ids)',
                'item_id = :item_id',
            ],
            'serializes' => [
                'answer' => 'json',
                'response_points' => 'json',
            ],
        ];
    }
}
