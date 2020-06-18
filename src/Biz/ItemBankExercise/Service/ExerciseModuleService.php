<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseModuleService
{
    public function findByExerciseId($exerciseId);

    public function get($id);
}
