<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerRecordService
{
    public function create($answerRecord = array());

    public function update($id, $answerRecord = array());

    public function get($id);

    public function getLatestAnswerRecordByAnswerSceneIdAndUserId($answerSceneId, $userId);

    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    public function count($conditions);

    public function getNextReviewingAnswerRecordByAnswerSceneId($answerSceneId);

    public function findByAnswerSceneId($answerSceneId);
}
