<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerReportService
{
    public function create(array $answerReport);

    public function update($id, array $answerReport);

    public function get($id);

    public function getSimple($id);

    public function findByIds(array $ids);

    public function findByAnswerSceneId($answerSceneId);

    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    public function count($conditions);

    public function wrapperAnswerQuestionReports($answerRecordId, $answerQuestionReports);
}
