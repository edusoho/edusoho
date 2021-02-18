<?php

namespace Tests\Assessment\Dao;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Tests\IntegrationTestCase;

class AssessmentDaoTest extends IntegrationTestCase
{
    public function testSearch()
    {
        $assessment = $this->mockAssessment();
        $secondAssessment = $this->mockAssessment(['name' => 'assessment', 'bank_id' => 2]);
        $result = $this->getAssessmentDao()->search(['id' => $assessment['id']], [], 0, 10);
        $this->assertEquals('test', $result[0]['name']);
        $this->assertCount(1, $result);

        $result = $this->getAssessmentDao()->search(['bank_id' => 2], [], 0, 10);
        $this->assertEquals('assessment', $result[0]['name']);
        $this->assertCount(1, $result);

        $result = $this->getAssessmentDao()->search(['nameLike' => 'te'], [], 0, 10);
        $this->assertEquals('test', $result[0]['name']);
        $this->assertCount(1, $result);

        $this->mockAssessment(['name' => 'testAssessment', 'status' => 'open']);
        $result = $this->getAssessmentDao()->search(['status' => 'open'], [], 0, 10);
        $this->assertEquals('testAssessment', $result[0]['name']);
        $this->assertCount(1, $result);

        $this->mockAssessment(['name' => 'test100', 'displayable' => 0]);
        $result = $this->getAssessmentDao()->search(['displayable' => 0], [], 0, 10);
        $this->assertEquals('test100', $result[0]['name']);
        $this->assertCount(1, $result);
    }

    protected function mockAssessment($assessment = [])
    {
        $default = [
            'bank_id' => 1,
            'name' => 'test',
            'displayable' => 1,
            'description' => 'test',
            'total_score' => 10,
            'item_count' => 5,
            'question_count' => 5,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $assessment = array_merge($default, $assessment);

        return $this->getAssessmentDao()->create($assessment);
    }

    /**
     * @return AssessmentDao
     */
    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }
}
