<?php

namespace Codeages\Biz\ItemBank\Item\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Item\Dao\QuestionFavoriteDao;

class QuestionFavoriteDaoImpl extends AdvancedDaoImpl implements QuestionFavoriteDao
{
    protected $table = 'biz_question_favorite';

    public function deleteByQuestionFavorite($questionFavorite)
    {
        return $this->db()->delete(
            $this->table,
            ['target_type' => $questionFavorite['target_type'], 'target_id' => $questionFavorite['target_id'], 'question_id' => $questionFavorite['question_id'], 'user_id' => $questionFavorite['user_id']]
        );
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
            ],
            'orderbys' => [
                'id',
                'created_time',
            ],
            'conditions' => [
                'question_id = :question_id',
                'item_id = :item_id',
                'target_id = :target_id',
                'target_ids IN (:target_ids)',
                'target_type = :target_type',
                'target_type IN (:target_types)',
                'user_id = :user_id',
                'user_id IN (:user_ids)',
            ],
            'serializes' => [],
        ];
    }
}
