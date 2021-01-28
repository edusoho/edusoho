<?php

namespace Tests\Assessment\Service;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionService;
use Tests\IntegrationTestCase;

class AssessmentSectionServiceTest extends IntegrationTestCase
{
    public function testGetAssessmentSection()
    {
        $section = $this->mockSection(['name' => 'section']);
        $result = $this->getAssessmentSectionService()->getAssessmentSection($section['id']);

        $this->assertEquals('section', $result['name']);
    }

    public function testFindSectionsByAssessmentId()
    {
        $this->mockSection(['name' => 'section', 'seq' => 2]);
        $this->mockSection();
        $result = $this->getAssessmentSectionService()->findSectionsByAssessmentId(1);

        $this->assertEquals('section', $result[1]['name']);
    }

    public function testFindSectionDetailByAssessmentId()
    {
        $sections[] = $this->mockSection(['name' => 'section', 'seq' => 2]);
        $sections[] = $this->mockSection();
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'findSectionItemDetailByAssessmentId',
                'returnValue' => [['section_id' => $sections[0]['id']]],
            ],
        ]);
        $result = $this->getAssessmentSectionService()->findSectionDetailByAssessmentId(1);

        $this->assertEquals('section', $result[1]['name']);
        $this->assertEmpty($result[0]['items']);
        $this->assertEquals(['section_id' => $sections[0]['id']], $result[1]['items'][0]);
    }

    public function testCreateAssessmentSection()
    {
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'createAssessmentSectionItem',
                'returnValue' => ['score' => 1, 'question_count' => 1],
            ],
        ]);

        $section = [
            'name' => 'sectionOne',
            'seq' => 1,
            'items' => [
                ['id' => 1, 'seq' => 1],
            ],
        ];

        $result = $this->getAssessmentSectionService()->createAssessmentSection(1, $section);

        $this->assertEquals('sectionOne', $result['name']);
        $this->assertEquals(1, $result['item_count']);
        $this->assertNotEmpty($result['items']);
    }

    public function testUpdateAssessmentSection()
    {
        $section = $this->mockSection();
        $result = $this->getAssessmentSectionService()->updateAssessmentSection($section['id'], ['name' => 'section']);

        $this->assertEquals('section', $result['name']);
    }

    public function testDeleteAssessmentSection()
    {
        $section = $this->mockSection();
        $this->getAssessmentSectionService()->deleteAssessmentSection($section['id']);

        $result = $this->getAssessmentSectionService()->getAssessmentSection($section['id']);
        $this->assertNull($result);
    }

    public function testDeleteAssessmentSectionsByAssessmentId()
    {
        $this->mockSection();
        $this->getAssessmentSectionService()->deleteAssessmentSectionsByAssessmentId(1);

        $result = $this->getAssessmentSectionService()->findSectionsByAssessmentId(1);
        $this->assertEmpty($result);
    }

    public function testCountAssessmentSections()
    {
        $section = $this->mockSection();
        $result = $this->getAssessmentSectionService()->countAssessmentSections(['id' => $section['id']]);

        $this->assertEquals(1, $result);
    }

    public function testSearchAssessmentSections()
    {
        $sections[] = $this->mockSection();
        $sections[] = $this->mockSection(['name' => 'section2']);
        $result = $this->getAssessmentSectionService()->searchAssessmentSections(
            ['id' => $sections[0]['id']],
            [],
            0,
            10
        );

        $this->assertEquals(1, count($result));
        $this->assertEquals('test', $result[0]['name']);
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
     * @return AssessmentSectionService
     */
    protected function getAssessmentSectionService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionService');
    }

    /**
     * @return AssessmentSectionDao
     */
    protected function getAssessmentSectionDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionDao');
    }
}
