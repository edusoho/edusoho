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

    public function countGroupByAnswerSceneId($conditions);

    public function batchCreateAnswerRecords($answerRecords);

    public function batchUpdateAnswerRecord($ids, $updateColumnsList);

    public function replaceAssessmentsWithSnapshotAssessments($assessmentSnapshots);

    public function findByIds($ids);

    public function countByAssessmentId($assessmentId);
}
