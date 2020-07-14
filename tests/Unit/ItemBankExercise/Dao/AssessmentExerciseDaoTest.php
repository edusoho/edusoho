<?php

namespace Tests\Unit\ItemBankExercise\Dao;

use Biz\BaseTestCase;
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
    protected function getItemBankAssessmentExerciseDao()
    {
        return $this->biz->dao('ItemBankExercise:AssessmentExerciseDao');
    }
}
