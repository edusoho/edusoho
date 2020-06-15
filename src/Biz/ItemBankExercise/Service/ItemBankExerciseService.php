<?php

namespace Biz\ItemBankExercise\Service;

interface ItemBankExerciseService
{
    public function countCourses($conditions);

    public function searchCourses($conditions, $orderBy, $start, $limit);
}
