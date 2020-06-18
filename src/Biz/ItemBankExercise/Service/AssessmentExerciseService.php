<?php

namespace Biz\ItemBankExercise\Service;

interface AssessmentExerciseService
{
    public function search($conditions, $sort, $start, $limit, $columns = []);

    public function count($conditions);
}
