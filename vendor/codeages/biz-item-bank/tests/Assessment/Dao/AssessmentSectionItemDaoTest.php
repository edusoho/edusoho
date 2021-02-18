<?php

namespace Tests\Assessment\Dao;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Tests\IntegrationTestCase;

class AssessmentSectionItemDaoTest extends IntegrationTestCase
{
    public function testFindByAssessmentId()
    {
        $sectionItem = $this->mockSectionItem(['seq' => 2]);
        $secondSectionItem = $this->mockSectionItem(['seq' => 1]);
        $result = $this->getAssessmentSectionItemDao()->findByAssessmentId(1);

        $this->assertEquals(2, count($result));
        $this->assertEquals(1, $result[0]['seq']);
        $this->assertEquals(2, $result[1]['seq']);
    }

    public function testDeleteByAssessmentId()
    {
        $section = $this->mockSectionItem(['assessment_id' => 1]);
        $secondSection = $this->mockSectionItem(['assessment_id' => 2]);
        $this->getAssessmentSectionItemDao()->deleteByAssessmentId(1);
        $result = $this->getAssessmentSectionItemDao()->get($secondSection['id']);

        $this->assertNotEmpty($result);
    }

    public function testSearch()
    {
        $section = $this->mockSectionItem();
        $secondSection = $this->mockSectionItem(['item_id' => '2']);
        $result = $this->getAssessmentSectionItemDao()->search(['id' => $section['id']], [], 0, 10);
        $this->assertEquals(1, $result[0]['item_id']);
        $this->assertEquals(1, count($result));
    }

    protected function mockSectionItem($sectionItem = [])
    {
        $default = [
            'assessment_id' => 1,
            'item_id' => 1,
            'section_id' => 1,
            'seq' => 1,
            'score' => 5,
            'question_scores' => [['question_id' => 1, 'score' => 2]],
            'score_rule' => [
                [
                    'question_id' => 1,
                    'rule' => [['name' => 'all_right', 'score' => 2], ['name' => 'no_answer', 'score' => 0], ['name' => 'wrong', 'score' => 0]],
                ],
            ],
        ];
        $sectionItem = array_merge($default, $sectionItem);

        return $this->getAssessmentSectionItemDao()->create($sectionItem);
    }

    /**
     * @return AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionItemDao');
    }
}
