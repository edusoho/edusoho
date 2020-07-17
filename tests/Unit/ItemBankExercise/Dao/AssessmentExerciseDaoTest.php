<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;
use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;

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

    public function testFindByExerciseIdAndModuleId()
    {
        $this->mockAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->findByExerciseIdAndModuleId(1, 1);

        $this->assertEquals(1, $res[0]['exerciseId']);
        $this->assertEquals(1, $res[0]['moduleId']);
        $this->assertEquals(1, $res[0]['assessmentId']);
    }

    public function testIsAssessmentExercise()
    {
        $this->mockAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->isAssessmentExercise(1, 1, 1);

        $this->assertEquals(1, $res['exerciseId']);
        $this->assertEquals(1, $res['moduleId']);
        $this->assertEquals(1, $res['assessmentId']);
    }

    /**
     * @return AssessmentExerciseDao
     */
    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseDao');
    }
}
