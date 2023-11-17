<?php

namespace Biz\Question\Dao\Impl;

use Biz\Question\Dao\QuestionFormulaImgRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuestionFormulaImgRecordDaoImpl extends AdvancedDaoImpl implements QuestionFormulaImgRecordDao
{
    protected $table = 'question_formula_img_record';

    public function findByFormulaHashes(array $formulaHashes)
    {
        return $this->findInField('formula_hash', $formulaHashes);
    }

    public function declares()
    {
        return [
            'timestamps' => ['created_time'],
        ];
    }
}
