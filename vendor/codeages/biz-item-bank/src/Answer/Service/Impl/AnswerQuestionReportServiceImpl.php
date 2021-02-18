<?php
namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class AnswerQuestionReportServiceImpl extends BaseService implements AnswerQuestionReportService
{
    public function batchCreate(array $answerQuestionReports)
    {
        return $this->getAnswerQuestionReportDao()->batchCreate($answerQuestionReports);
    }

    public function findByAnswerRecordId($answerRecordId)
    {
        return $this->getAnswerQuestionReportDao()->findByAnswerRecordId($answerRecordId);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getAnswerQuestionReportDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function count($conditions)
    {
        return $this->getAnswerQuestionReportDao()->count($conditions);
    }

    public function batchUpdate(array $answerQuestionReports)
    {
        return $this->getAnswerQuestionReportDao()->batchUpdate(ArrayToolkit::column($answerQuestionReports, 'identify'), $answerQuestionReports, 'identify');
    }

    protected function getAnswerQuestionReportDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerQuestionReportDao');
    }
}
