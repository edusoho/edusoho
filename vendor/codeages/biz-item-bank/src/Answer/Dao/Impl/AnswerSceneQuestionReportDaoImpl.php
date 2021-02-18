<?php
namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerSceneQuestionReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class AnswerSceneQuestionReportDaoImpl extends AdvancedDaoImpl implements AnswerSceneQuestionReportDao
{
    protected $table = 'biz_answer_scene_question_report';

    public function findByAnswerSceneId($answerSceneId)
    {
        return $this->findByFields(['answer_scene_id' => $answerSceneId]);
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time'
            ],
            'orderbys' => [
                'id'
            ],
            'serializes' => [
                'response_points_report' => 'json'
            ],
            'conditions' => [
               'answer_scene_id = :answer_scene_id'
            ],
        ];
    }
}
