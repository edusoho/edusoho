<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface HomeworkActivityDao extends GeneralDaoInterface
{
    public function getByAnswerSceneId($answerSceneId);

    public function getByAssessmentId($assessmentId);

    public function findByIds($ids);

    public function findByAnswerSceneIds($answerSceneIds);
}
