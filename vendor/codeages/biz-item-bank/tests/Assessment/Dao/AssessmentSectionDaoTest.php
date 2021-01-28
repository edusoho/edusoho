<?php

namespace Tests\Assessment\Dao;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionDao;
use Tests\IntegrationTestCase;

class AssessmentSectionDaoTest extends IntegrationTestCase
{
    public function testFindByAssessmentId()
    {
        $section = $this->mockSection(['seq' => 2]);
        $secondSection = $this->mockSection(['seq' => 1]);
        $result = $this->getAssessmentSectionDao()->findByAssessmentId(1);

        $this->assertEquals(2, count($result));
        $this->assertEquals(1, $result[0]['seq']);
        $this->assertEquals(2, $result[1]['seq']);
    }

    public function testDeleteByAssessmentId()
    {
        $section = $this->mockSection(['assessment_id' => 1]);
        $secondSection = $this->mockSection(['assessment_id' => 2]);
        $this->getAssessmentSectionDao()->deleteByAssessmentId(1);
        $result = $this->getAssessmentSectionDao()->get($secondSection['id']);

        $this->assertNotEmpty($result);
    }

    public function testSearch()
    {
        $section = $this->mockSection();
        $secondSection = $this->mockSection(['name' => 'section']);
        $result = $this->getAssessmentSectionDao()->search(['id' => $section['id']], [], 0, 10);
        $this->assertEquals('test', $result[0]['name']);
        $this->assertEquals(1, count($result));
    }

    protected function mockSection($section = [])
    {
        $default = [
            'assessment_id' => 1,
            'name' => 'test',
            'seq' => 1,
            'description' => 'test',
            'item_count' => 5,
            'question_count' => 5,
            'score_rule' => '',
            'total_score' => 5,
        ];
        $section = array_merge($default, $section);

        return $this->getAssessmentSectionDao()->create($section);
    }

    /**
     * @return AssessmentSectionDao
     */
    protected function getAssessmentSectionDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionDao');
    }
}
