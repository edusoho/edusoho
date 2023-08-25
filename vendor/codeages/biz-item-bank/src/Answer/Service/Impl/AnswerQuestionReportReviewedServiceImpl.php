<?php
namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\ItemBank\Answer\Dao\AnswerQuestionReportReviewedDao;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportReviewedService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class AnswerQuestionReportReviewedServiceImpl extends BaseService implements AnswerQuestionReportReviewedService
{
    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerQuestionReportReviewedDao()->findByAnswerRecordId($answerRecordId);
    }

    public function countByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerQuestionReportReviewedDao()->countByAnswerRecordId($answerRecordId);
    }

    public function getByAnswerRecordIdAndQuestionId($recordId, $questionId)
    {
        return $this->getAnswerQuestionReportReviewedDao()->getByAnswerRecordIdAndQuestionId($recordId, $questionId);
    }

    public function createAnswerQuestionReportReviewed($AnswerQuestionReportReviewed)
    {
        return $this->getAnswerQuestionReportReviewedDao()->create($AnswerQuestionReportReviewed);
    }

    /**
     * @return AnswerQuestionReportReviewedDao
     */
    protected function getAnswerQuestionReportReviewedDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportReviewedDao');
    }
}
