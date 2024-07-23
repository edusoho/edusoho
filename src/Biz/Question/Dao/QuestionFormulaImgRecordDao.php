<?php

namespace Biz\Question\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface QuestionFormulaImgRecordDao extends AdvancedDaoInterface
{
    public function findByFormulaHashes(array $formulaHashes);
}
