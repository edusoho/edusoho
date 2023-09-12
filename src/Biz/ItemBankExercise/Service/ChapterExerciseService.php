<?php

namespace Biz\ItemBankExercise\Service;

interface ChapterExerciseService
{
    public function startAnswer($moduleId, $categroyId, $userId, $exerciseMode = 0);

    public function findChaptersByIds($ids);

    public function getChapterTree($itemBankId);

    public function getChapterTreeList($itemBankId);

    public function findChapterChildrenIds($id);
}
