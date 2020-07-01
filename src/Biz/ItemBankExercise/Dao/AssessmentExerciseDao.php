<?php

namespace Biz\ItemBankExercise\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AssessmentExerciseDao extends GeneralDaoInterface
{
    public function findByModuleId($moduleId);
}
