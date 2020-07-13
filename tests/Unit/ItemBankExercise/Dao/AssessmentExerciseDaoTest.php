<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;

class AssessmentExerciseDaoTest extends BaseTestCase
{
    public function testGetByModuleIdAndAssessmentId()
    {
        $this->mockAssessmentExercise();

        $assessmentExercise = $this->getItemBankAssessmentExerciseDao()->getByModuleIdAndAssessmentId(1, 1);

        $this->assertEquals($assessmentExercise['moduleId'], 1);
        $this->assertEquals($assessmentExercise['assessmentId'], 1);
    }

    protected function mockAssessmentExercise()
    {
        $this->getItemBankAssessmentExerciseDao()->create([
            'exerciseId' => 1,
            'moduleId' => 1,
            'assessmentId' => 1,
        ]);
    }

    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseDao');
    }
}
