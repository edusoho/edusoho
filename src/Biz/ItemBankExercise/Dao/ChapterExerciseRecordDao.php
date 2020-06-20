<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ChapterExerciseRecordDao extends GeneralDaoInterface
{
    public function getByAnswerRecordId($answerRecordId);
}
