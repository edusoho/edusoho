<?php

namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Biz\Question\Dao\QuestionFormulaImgRecordDao;
use Biz\Question\Service\QuestionService;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    public function createQuestionFormulaImgRecords(array $questionFormulaImgRecords)
    {
        $this->getQuestionFormulaImgRecordDao()->batchCreate($questionFormulaImgRecords);
    }

    public function findQuestionFormulaImgRecordsByFormulas(array $formulas)
    {
        $formulaHashes = array_map('md5', $formulas);
        $records = $this->getQuestionFormulaImgRecordDao()->findByFormulaHashes($formulaHashes);
        $records = array_column($records, null, 'formula');
        $sortedRecords = [];
        foreach ($formulas as $formula) {
            if (isset($records[$formula])) {
                $sortedRecords[] = $records[$formula];
            }
        }

        return $sortedRecords;
    }

    /**
     * @return QuestionFormulaImgRecordDao
     */
    protected function getQuestionFormulaImgRecordDao()
    {
        return $this->createDao('Question:QuestionFormulaImgRecordDao');
    }
}
