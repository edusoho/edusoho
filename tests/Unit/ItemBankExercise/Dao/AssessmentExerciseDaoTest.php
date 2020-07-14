<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;
<<<<<<< HEAD
use Biz\ItemBankExercise\Dao\AssessmentExerciseDao;

class AssessmentExerciseDaoTest extends BaseTestCase
{
    public function testFindByExerciseIdAndModuleId()
    {
        $excepted = $this->createAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->findByExerciseIdAndModuleId($excepted['exerciseId'], $excepted['moduleId']);

        $this->assertEquals($excepted['exerciseId'], $res[0]['exerciseId']);
        $this->assertEquals($excepted['moduleId'], $res[0]['moduleId']);
        $this->assertEquals($excepted['assessmentId'], $res[0]['assessmentId']);
    }

    public function testIsAssessmentExercise()
    {
        $excepted = $this->createAssessmentExercise();
        $res = $this->getItemBankAssessmentExerciseDao()->isAssessmentExercise($excepted['moduleId'],$excepted['assessmentId'], $excepted['exerciseId']);

        $this->assertEquals($excepted['exerciseId'], $res['exerciseId']);
        $this->assertEquals($excepted['moduleId'], $res['moduleId']);
        $this->assertEquals($excepted['assessmentId'], $res['assessmentId']);
    }

    protected function createAssessmentExercise()
    {
        return $this->getItemBankAssessmentExerciseDao()->create(
            [
                'exerciseId' => 1,
                'moduleId' => 1,
                'assessmentId' => 1,
            ]
        );
    }

    /**
     * @return AssessmentExerciseDao
     */
=======

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

>>>>>>> a6d69cf857e21cbdbdf3718ba5ca22c8759e27ee
    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseDao');
    }
}
