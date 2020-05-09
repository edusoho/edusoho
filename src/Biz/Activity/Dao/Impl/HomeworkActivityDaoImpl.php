<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\HomeworkActivityDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class HomeworkActivityDaoImpl extends GeneralDaoImpl implements HomeworkActivityDao
{
    protected $table = 'activity_homework';

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getByFields(array('answerSceneId' => $answerSceneId));
    }

    public function getByAssessmentId($assessmentId)
    {
        return $this->getByFields(array('assessmentId' => $assessmentId));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->findInField('answerSceneId', $answerSceneIds);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'answerSceneId = :answerSceneId',
                'assessmentId = :assessmentId',
            ),
        );
    }
}
