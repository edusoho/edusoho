<?php

namespace Biz\ItemBankExercise\Service;

interface ChapterExerciseService
{
    public function startAnswer($moduleId, $categroyId, $userId, $exerciseMode = 0);

    public function findChaptersByIds($ids);
}
