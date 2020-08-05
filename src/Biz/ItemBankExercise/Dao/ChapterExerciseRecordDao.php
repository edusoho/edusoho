<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ChapterExerciseRecordDao extends GeneralDaoInterface
{
    public function getByAnswerRecordId($answerRecordId);

    public function getLatestRecord($moduleId, $itemCategoryId, $userId);

    public function findWeekRankRecords($exerciseId);
}
