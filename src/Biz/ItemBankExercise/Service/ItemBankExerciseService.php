<?php

namespace Biz\ItemBankExercise\Service;

interface ItemBankExerciseService
{
    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);
}
