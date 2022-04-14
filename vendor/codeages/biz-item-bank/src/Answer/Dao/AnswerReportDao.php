<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AnswerReportDao extends AdvancedDaoInterface
{
    public function findByIds(array $ids);

    public function findByAnswerSceneId($answerSceneId);

    public function deleteByAssessmentId($assessmentId);
}
