<?php

namespace Tests\Answer\Service;

use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Tests\IntegrationTestCase;

class AnswerRandomSeqServiceTest extends IntegrationTestCase
{
    public function testCreateAnswerRandomSeqRecordIfNecessary_whenRecordNotExists()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [],
            ],
        ]);
        $answerRandomSeqRecord = $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary(1);
        $this->assertEmpty($answerRandomSeqRecord);
    }

    public function testCreateAnswerRandomSeqRecordIfNecessary_whenRecordRandomSeqDisable()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [
                    'is_items_seq_random' => 0,
                    'is_options_seq_random' => 0,
                ],
            ],
        ]);
        $answerRandomSeqRecord = $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary(1);
        $this->assertEmpty($answerRandomSeqRecord);
    }

    public function testCreateAnswerRandomSeqRecordIfNecessary_whenAssessmentNoSectionItems()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [
                    'assessment_id' => 1,
                    'is_items_seq_random' => 1,
                    'is_options_seq_random' => 1,
                ],
            ],
        ]);
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'searchAssessmentSectionItems',
                'returnValue' => [],
            ]
        ]);
        $answerRandomSeqRecord = $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary(1);
        $this->assertEmpty($answerRandomSeqRecord);
    }

    public function testCreateAnswerRandomSeqRecordIfNecessary_whenOnlyItemRandomSeqEnable()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                    'assessment_id' => 1,
                    'is_items_seq_random' => 1,
                    'is_options_seq_random' => 0,
                ],
            ],
        ]);
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'searchAssessmentSectionItems',
                'returnValue' => [
                    [
                        'section_id' => 1,
                        'item_id' => 1,
                    ],
                    [
                        'section_id' => 1,
                        'item_id' => 2,
                    ],
                    [
                        'section_id' => 1,
                        'item_id' => 3,
                    ],
                ],
            ]
        ]);
        $answerRandomSeqRecord = $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary(1);
        $itemIds = $answerRandomSeqRecord['items_random_seq'][1];
        sort($itemIds);
        $this->assertEquals(1, $answerRandomSeqRecord['answer_record_id']);
        $this->assertEquals([1, 2, 3], $itemIds);
        $this->assertEmpty($answerRandomSeqRecord['options_random_seq']);
    }

    public function testCreateAnswerRandomSeqRecordIfNecessary_whenOnlyOptionRandomSeqEnableAndAssessmentNoChoiceItem()
    {
        $this->mockObjectIntoBiz('ItemBank:Answer:AnswerRecordService', [
            [
                'functionName' => 'get',
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                    'assessment_id' => 1,
                    'is_items_seq_random' => 0,
                    'is_options_seq_random' => 1,
                ],
            ],
        ]);
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'searchAssessmentSectionItems',
                'returnValue' => [
                    [
                        'section_id' => 1,
                        'item_id' => 1,
                    ],
                    [
                        'section_id' => 1,
                        'item_id' => 2,
                    ],
                    [
                        'section_id' => 1,
                        'item_id' => 3,
                    ],
                ],
            ]
        ]);
        $this->mockObjectIntoBiz('ItemBank:Item:ItemService', [
            [
                'functionName' => 'searchItems',
                'returnValue' => [[]],
            ],
        ]);
        $answerRandomSeqRecord = $this->getAnswerRandomSeqService()->createAnswerRandomSeqRecordIfNecessary(1);
        $this->assertEquals(1, $answerRandomSeqRecord['answer_record_id']);
        $this->assertEmpty($answerRandomSeqRecord['items_random_seq']);
        $this->assertEmpty($answerRandomSeqRecord['options_random_seq']);
    }

    /**
     * @return AnswerRandomSeqService
     */
    protected function getAnswerRandomSeqService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRandomSeqService');
    }
}
