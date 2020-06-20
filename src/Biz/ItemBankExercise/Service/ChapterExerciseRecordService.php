<?php

namespace Biz\ItemBankExercise\Service;

interface ChapterExerciseRecordService
{
    public function create($chapterExerciseRecord);

    public function get($id);

    public function update($id, $chapterExerciseRecord);

    public function getByAnswerRecordId($answerRecordId);
}
