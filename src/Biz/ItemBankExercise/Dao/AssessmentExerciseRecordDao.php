<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AssessmentExerciseRecordDao extends GeneralDaoInterface
{
    public function getByAnswerRecordId($answerRecordId);

    public function getLatestRecord($moduleId, $assessmentId, $userId);
}
