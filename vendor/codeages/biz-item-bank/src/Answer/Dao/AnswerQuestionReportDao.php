<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AnswerQuestionReportDao extends AdvancedDaoInterface
{
    public function findByIds($ids);

    public function findByAnswerRecordId($answerRecordId);

    public function deleteByAssessmentId($assessmentId);

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);

    public function batchUpdateByTwoIdentify($whereIdentifyColumn, $whereIdentifies, $caseIdentifyColumn, $caseIdentifies, $updateColumnsList);
}
