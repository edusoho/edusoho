<?php

namespace Biz\Activity\Service\Impl;

use Biz\Activity\Dao\HomeworkActivityDao;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\BaseService;

class HomeworkActivityServiceImpl extends BaseService implements HomeworkActivityService
{
    public function create($homeworkActivity)
    {
        return $this->getHomeworkActivityDao()->create($homeworkActivity);
    }

    public function batchUpdate($identifies, $updateColumnsList, $identifyColumn = 'id')
    {
        return $this->getHomeworkActivityDao()->batchUpdate($identifies, $updateColumnsList, $identifyColumn);
    }

    public function update($homeworkActivityId, array $fields)
    {
        return $this->getHomeworkActivityDao()->update($homeworkActivityId, $fields);
    }

    public function getByAnswerSceneId($answerSceneId)
    {
        return $this->getHomeworkActivityDao()->getByAnswerSceneId($answerSceneId);
    }

    public function getByAssessmentId($assessmentId)
    {
        return $this->getHomeworkActivityDao()->getByAssessmentId($assessmentId);
    }

    public function get($id)
    {
        return $this->getHomeworkActivityDao()->get($id);
    }

    public function findByIds($ids)
    {
        return $this->getHomeworkActivityDao()->findByIds($ids);
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->getHomeworkActivityDao()->findByAnswerSceneIds($answerSceneIds);
    }

    public function findByAssessmentId($assessmentId)
    {
        return $this->getHomeworkActivityDao()->findByAssessmentId($assessmentId);
    }

    public function delete($id)
    {
        return $this->getHomeworkActivityDao()->delete($id);
    }

    /**
     * @return HomeworkActivityDao
     */
    protected function getHomeworkActivityDao()
    {
        return $this->createDao('Activity:HomeworkActivityDao');
    }
}
