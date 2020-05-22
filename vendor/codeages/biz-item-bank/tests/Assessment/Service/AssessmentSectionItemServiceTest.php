<?php

namespace Tests\Assessment\Service;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Tests\IntegrationTestCase;

class AssessmentSectionItemServiceTest extends IntegrationTestCase
{
    public function testGetAssessmentSectionItem()
    {
        $item = $this->mockItem(['item_id' => 2]);
        $result = $this->getSectionItemService()->getAssessmentSectionItem($item['id']);

        $this->assertEquals(2, $result['item_id']);
    }

    public function testFindSectionItemsByAssessmentId()
    {
        $items[] = $this->mockItem();
        $items[] = $this->mockItem();
        $items[] = $this->mockItem(['assessment_id' => 2]);
        $result = $this->getSectionItemService()->findSectionItemsByAssessmentId(1);

        $this->assertEquals(2, count($result));
    }

    public function testFindSectionItemDetailByAssessmentId()
    {
        $this->mockObjectIntoBiz('ItemBank:Item:ItemService', [
            [
                'functionName' => 'findItemsByIds',
                'returnValue' => ['1' => ['id' => 1, 'questions' => [['id' => 1]]]],
            ],
        ]);

        $this->mockItem();
        $result = $this->getSectionItemService()->findSectionItemDetailByAssessmentId(1);

        $this->assertEquals(1, $result[0]['questions'][0]['miss_score']);
        $this->assertEquals(2, $result[0]['questions'][0]['score']);
        $this->assertEquals(2, $result[0]['score']);
    }

    public function testCreateAssessmentSectionItem()
    {
        $item = [
            'id' => 5,
            'seq' => 1,
            'questions' => [
                ['id' => 1, 'score' => 2, 'seq' => 1],
            ],
        ];
        $result = $this->getSectionItemService()->createAssessmentSectionItem($item, ['id' => 1, 'assessment_id' => 1]);

        $this->assertEquals([
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ], $result['score_rule'][0]['rule']);
        $this->assertEquals(2, $result['score']);
        $this->assertEquals(1, $result['question_count']);
    }

    public function testUpdateAssessmentSectionItem()
    {
        $item = $this->mockItem();
        $result = $this->getSectionItemService()->updateAssessmentSectionItem($item['id'], ['assessment_id' => 2]);

        $this->assertEquals(2, $result['assessment_id']);
    }

    public function testDeleteAssessmentSectionItem()
    {
        $item = $this->mockItem();
        $this->getSectionItemService()->deleteAssessmentSectionItem($item['id']);

        $result = $this->getSectionItemService()->getAssessmentSectionItem($item['id']);
        $this->assertEmpty($result);
    }

    public function testDeleteAssessmentSectionItemsByAssessmentId()
    {
        $this->mockItem();
        $this->mockItem();
        $this->mockItem(['assessment_id' => 2]);
        $this->getSectionItemService()->deleteAssessmentSectionItemsByAssessmentId(1);
        $result = $this->getSectionItemService()->findSectionItemsByAssessmentId(1);

        $this->assertEmpty($result);
    }

    public function testCountAssessmentSectionItems()
    {
        $items[] = $this->mockItem();
        $items[] = $this->mockItem();
        $result = $this->getSectionItemService()->countAssessmentSectionItems(['id' => $items[0]['id']]);

        $this->assertEquals(1, $result);
    }

    public function testSearchAssessmentSectionItems()
    {
        $items[] = $this->mockItem(['assessment_id' => 2]);
        $items[] = $this->mockItem();
        $result = $this->getSectionItemService()->searchAssessmentSectionItems(
            ['id' => $items[0]['id']],
            [],
            0,
            10
        );

        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result[0]['assessment_id']);
    }

    protected function mockItem($item = [])
    {
        $default = [
            'assessment_id' => 1,
            'item_id' => 1,
            'section_id' => 1,
            'seq' => 1,
            'score' => 2,
            'question_scores' => [['question_id' => 1, 'score' => 2]],
            'score_rule' => [
                [
                    'question_id' => 1,
                    'seq' => 1,
                    'rule' => [['name' => 'all_right', 'score' => 2], ['name' => 'part_right', 'score' => 1], ['name' => 'no_answer', 'score' => 0], ['name' => 'wrong', 'score' => 0]],
                ],
            ],
        ];
        $item = array_merge($default, $item);

        return $this->getAssessmentSectionItemDao()->create($item);
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionItemDao');
    }
}
