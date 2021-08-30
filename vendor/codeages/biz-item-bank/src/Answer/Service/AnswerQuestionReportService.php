<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerQuestionReportService
{
    const STATUS_RIGHT = 'right';

    const STATUS_WRONG = 'wrong';

    const STATUS_REVIEWING = 'reviewing';

    const STATUS_NOANSWER = 'no_answer';

    const STATUS_PART_RIGHT = 'part_right';
    
    public function batchCreate(array $answerQuestionReports);

    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    public function count($conditions);
  
    public function batchUpdate(array $answerQuestionReports);

    public function findByAnswerRecordId($answerRecordId);
}
