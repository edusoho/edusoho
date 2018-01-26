<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionAnalysisDao extends GeneralDaoInterface
{
    public function getAnalysisItem($targetId, $targetType, $questionId, $choiceIndex);

    public function findByTargetIdAndTargetType($targetId, $targetType);

    public function findByTargetIdAndTargetTypeAndQuestionId($targetId, $targetType, $questionId);
}
