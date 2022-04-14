<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\HomeworkActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class HomeworkActivityDaoImpl extends AdvancedDaoImpl implements HomeworkActivityDao
{
    protected $table = 'activity_homework';

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getByFields(['answerSceneId' => $answerSceneId]);
    }

    public function getByAssessmentId($assessmentId)
    {
        return $this->getByFields(['assessmentId' => $assessmentId]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->findInField('answerSceneId', $answerSceneIds);
    }

    public function findByAssessmentId($assessmentId)
    {
        return $this->findByFields(['assessmentId' => $assessmentId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'answerSceneId = :answerSceneId',
                'assessmentId = :assessmentId',
                /*S2B2C增加syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
            'serializes' => [
                'finish_condition' => 'json',
            ],
        ];
    }
}
