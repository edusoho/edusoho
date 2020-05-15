<?php

namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerReportDaoImpl extends AdvancedDaoImpl implements AnswerReportDao
{
    protected $table = 'biz_answer_report';

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByAnswerSceneId($answerSceneId)
    {
        return $this->findByFields(['answer_scene_id' => $answerSceneId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [],
            'serializes' => [],
            'conditions' => [
                'answer_scene_id = :answer_scene_id',
                'status = :status',
                'review_user_id = :review_user_id',
            ],
        ];
    }
}
