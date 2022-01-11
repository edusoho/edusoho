<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface HomeworkActivityDao extends AdvancedDaoInterface
{
    public function getByAnswerSceneId($answerSceneId);

    public function getByAssessmentId($assessmentId);

    public function findByIds($ids);

    public function findByAnswerSceneIds($answerSceneIds);

    public function findByAssessmentId($assessmentId);
}
