<?php

namespace Biz\ItemBankExercise\Service;

use Biz\System\Annotation\Log;

interface ItemBankExerciseService
{
    public function countCourses($conditions);

    public function searchCourses($conditions, $orderBy, $start, $limit);
}
