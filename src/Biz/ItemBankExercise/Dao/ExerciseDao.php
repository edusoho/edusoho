<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ExerciseDao extends AdvancedDaoInterface
{
    public function getByQuestionBankId($questionBankId);

    public function findByIds($ids);
}
