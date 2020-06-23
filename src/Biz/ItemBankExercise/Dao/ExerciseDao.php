<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ExerciseDao extends GeneralDaoInterface
{
    public function updateCategoryByExerciseId($exerciseId, $fields);

    public function getByQuestionBankId($questionBankId);
}
