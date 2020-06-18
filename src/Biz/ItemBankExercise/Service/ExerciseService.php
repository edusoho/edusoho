<?php

namespace Biz\ItemBankExercise\Service;

interface ExerciseService
{
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);

    public function get($id);
}
