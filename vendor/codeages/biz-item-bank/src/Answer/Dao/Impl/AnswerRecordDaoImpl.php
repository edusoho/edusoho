<?php

namespace Codeages\Biz\ItemBank\Answer\Dao\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerRecordDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AnswerRecordDaoImpl extends GeneralDaoImpl implements AnswerRecordDao
{
    protected $table = 'biz_answer_record';

    public function getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE answer_scene_id = ? AND user_id = ? ORDER BY id DESC LIMIT 1';

        return $this->db()->fetchAssoc($sql, [$answerSceneId, $userId]);
    }

    public function getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId)
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE answer_scene_id = ? AND status = "reviewing" ORDER BY begin_time ASC LIMIT 1';

        return $this->db()->fetchAssoc($sql, [$answerSceneId]);
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
            'orderbys' => [
                'created_time',
                'updated_time',
                'begin_time',
                'end_time',
            ],
            'serializes' => [],
            'conditions' => [
                'answer_scene_id = :answer_scene_id',
                'user_id = :user_id',
                'user_id in (:user_ids)',
                'id in (:ids)',
                'status = :status',
                'status <> :statusNeq',
                'begin_time > :beginTime_GT',
                'begin_time <= :beginTime_ELT',
                'answer_scene_id IN (:answer_scene_ids)',
                'assessment_id = :assessment_id',
            ],
        ];
    }
}
