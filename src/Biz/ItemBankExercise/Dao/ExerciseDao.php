<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseDao extends GeneralDaoInterface
{
    public function getByQuestionBankId($questionBankId);

    public function findByIds($ids);
}
