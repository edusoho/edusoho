<?php

namespace Biz\ItemBankExercise\Service;

interface AssessmentExerciseRecordService
{
    public function create($assessmentExerciseRecord);

    public function update($id, $assessmentExerciseRecord);

    public function search($conditions, $sort, $start, $limit, $columns = []);

    public function count($conditions);

    public function getByAnswerRecordId($answerRecordId);

    public function getLatestRecord($moduleId, $assessmentId, $userId);
}
