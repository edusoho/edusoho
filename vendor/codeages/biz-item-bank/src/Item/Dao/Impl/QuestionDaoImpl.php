<?php

namespace Codeages\Biz\ItemBank\Item\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Dao\SoftDelete;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;

class QuestionDaoImpl extends AdvancedDaoImpl implements QuestionDao
{
    use SoftDelete;

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
            'orderbys' => [
                'id',
                'created_time',
                'updated_time',
            ],
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'id in (:ids)',
                'item_id = :item_id',
                'item_id in (:item_ids)',
            ],
            'serializes' => [
                'answer' => 'json',
                'response_points' => 'json',
                'score_rule' => 'json',
            ],
        ];
    }
}
