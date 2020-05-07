<?php

namespace Tests\Assessment\Service;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionDao;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Tests\IntegrationTestCase;

class AssessmentServiceTest extends IntegrationTestCase
{
    public function testGetAssessment()
    {
        $mockAssessment = $this->mockAssessment();
        $assessment = $this->getAssessmentService()->getAssessment($mockAssessment['id']);

        $this->assertEquals('test', $assessment['name']);
    }

    public function testGetAssessment_whenIdMIss_thenReturnEmpty()
    {
        $assessment = $this->getAssessmentService()->getAssessment(2);

        $this->assertEmpty($assessment['name']);
    }

    public function testShowAssessment()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $assessment = $this->mockCompleteAssessment();
        $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);

        $this->assertEquals('test', $assessment['sections'][0]['name']);
        $this->assertEquals('test', $assessment['name']);
        $this->assertEquals('single_choice', $assessment['sections'][0]['items'][0]['questions'][0]['answer_mode']);
        $this->assertEquals(1, $assessment['sections'][0]['items'][1]['isDelete']);
        $this->assertEquals(1, $assessment['sections'][0]['items'][0]['questions'][0]['miss_score']);
    }

    public function testShowAssessment_whenMissSection_thenReturnAssessmentWithEmptySection()
    {
        $assessment = $this->mockAssessment();
        $this->mockSection(['assessment_id' => ($assessment['id'] + 1)]);
        $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);

        $this->assertEmpty($assessment['sections']);
    }

    public function testShowAssessment_whenMissItem_thenReturnAssessmentWithEmptyItem()
    {
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $this->mockSectionItem(['section_id' => $section['id'] + 1, 'assessment_id' => $assessment['id']]);
        $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);

        $this->assertEmpty($assessment['sections'][0]['items']);
    }

    public function testCreateAssessment()
    {
        $this->mockItemBankService();
        $assessment = [
            'bank_id' => 1,
            'name' => 'testAssessment',
            'description' => 'test',
            'displayable' => 1,
            'sections' => [
                [
                    'name' => 'testSection',
                    'description' => 'testDescription',
                    'items' => [
                        [
                            'id' => 1,
                            'questions' => [['id' => 2, 'score' => 2]],
                        ],
                    ],
                ],
            ],
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->assertEquals('testSection', $assessment['sections'][0]['name']);
        $this->assertEquals(1, $assessment['sections'][0]['seq']);
        $this->assertEquals(1, $assessment['sections'][0]['items'][0]['seq']);
        $this->assertEquals('testAssessment', $assessment['name']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testCreateAssessment_whenParamError_thenThrowException()
    {
        $this->mockItemBankService();
        $assessment = [
            'bank_id' => -1,
            'name' => 'testAssessment',
            'created_user_id' => 1,
            'sections' => [],
        ];
        $this->getAssessmentService()->createAssessment($assessment);
    }

    public function testImportAssessment()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $assessment = [
            'bank_id' => 5,
            'name' => 'test',
            'displayable' => 1,
            'sections' => [
                [
                    'name' => 'testSection',
                    'seq' => 1,
                    'description' => 'testDescription',
                    'item_type' => 'single_choice',
                    'total_score' => 10,
                    'items' => [
                        [
                            'seq' => 1,
                            'score' => 2,
                            'type' => 'single_choice',
                            'difficulty' => 'normal',
                            'material' => '',
                            'analysis' => '',
                            'attachments' => [],
                            'questions' => [[
                                'score' => '2.0',
                                'answer_mode' => 'single_choice',
                                'stem' => '<p>这是题干</p>',
                                'analysis' => '<p>这是解析</p>',
                                'seq' => 1,
                                'attachments' => [],
                                'response_points' => [
                                    [
                                        'radio' => [
                                            'val' => 'A',
                                            'text' => '选项A',
                                        ],
                                    ],
                                    [
                                        'radio' => [
                                            'val' => 'B',
                                            'text' => '选项B',
                                        ],
                                    ],
                                    [
                                        'radio' => [
                                            'val' => 'C',
                                            'text' => '选项C',
                                        ],
                                    ],
                                    [
                                        'radio' => [
                                            'val' => 'D',
                                            'text' => '选项D',
                                        ],
                                    ],
                                ],
                                'answer' => ['A'],
                            ]],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->getAssessmentService()->importAssessment($assessment);

        $this->assertEquals(1, count($result['sections']));
        $this->assertEquals(1, count($result['sections'][0]['items']));
        $this->assertEquals(5, $result['bank_id']);
        $this->assertEquals('test', $result['name']);
    }

    public function testDeleteAssessment()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ]);
        $this->getAssessmentService()->deleteAssessment($assessment['id']);
        $assessment = $this->getAssessmentService()->getAssessment($assessment['id']);

        $this->assertEmpty($assessment);
    }

    public function testUpdateAssessment()
    {
        $assessment = $this->mockAssessment();
        $updateFields = [
            'bank_id' => 5,
            'name' => 'testAssessment',
            'sections' => [
                [
                    'name' => 'testSection',
                    'seq' => 1,
                    'description' => 'testDescription',
                    'items' => [
                        [
                            'id' => 1,
                            'seq' => 1,
                            'questions' => [['id' => 2, 'score' => 2]],
                        ],
                    ],
                ],
            ],
        ];
        $assessment = $this->getAssessmentService()->updateAssessment($assessment['id'], $updateFields);

        $this->assertEquals('testAssessment', $assessment['name']);
        $this->assertEquals(0, $assessment['updated_user_id']);
        $this->assertEquals(1, $assessment['item_count']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     */
    public function testUpdateAssessment_whenMissAssessment_thenThrowException()
    {
        $assessment = $this->getAssessmentService()->updateAssessment(-1, [
            'name' => 'testAssessment',
            'updated_user_id' => 2,
        ]);
    }

    public function testDrawItems()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $options = ['bank_id' => 1];
        $sections = [
            [
                'conditions' => ['item_types' => ['single_choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
        ];
        $this->mockItem(['type' => 'single_choice', 'category_id' => 2, 'difficulty' => 'simple']);
        $this->mockItem(['type' => 'single_choice']);
        $this->mockItem(['type' => 'single_choice', 'category_id' => 2, 'difficulty' => 'difficulty']);
        $this->mockItem(['type' => 'choice', 'category_id' => 2, 'difficulty' => 'difficulty']);
        $this->mockItem(['type' => 'choice']);
        $this->mockItem(['type' => 'choice']);

        $result = $this->getAssessmentService()->drawItems($options, $sections);
        $this->assertEquals('difficulty', $result[0]['items'][0]['difficulty']);
        $this->assertEquals('difficulty', $result[1]['items'][0]['difficulty']);
        $this->assertEquals('single_choice', $result[0]['items'][0]['type']);
        $this->assertEquals('choice', $result[1]['items'][0]['type']);

        $sections = [
            [
                'conditions' => ['item_types' => ['single_choice', 'choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
        ];
        $result = $this->getAssessmentService()->drawItems($options, $sections);
        $this->assertEquals(1, count($result['0']['items']));
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testDrawItems_whenItemEmpty_thenReturnMissSection()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $options = ['bank_id' => 1];
        $sections = [
            [
                'conditions' => ['item_types' => ['single_choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
        ];
        $result = $this->getAssessmentService()->drawItems($options, $sections);
    }

    public function testCountAssessments()
    {
        $assessment = $this->mockAssessment();
        $assessment = $this->mockAssessment();

        $count = $this->getAssessmentService()->countAssessments(array());
        $this->assertEquals(2, $count);
    }

    public function testSearchAssessments()
    {
        $assessment = $this->mockAssessment(['bank_id' => 2]);
        $assessment = $this->mockAssessment();

        $assessments = $this->getAssessmentService()->searchAssessments([], [], 0, 10);
        $this->assertEquals(2, count($assessments));

        $assessments = $this->getAssessmentService()->searchAssessments(['bank_id' => 2], [], 0, 10);
        $this->assertEquals(1, count($assessments));
        $this->assertEquals(2, $assessments[0]['bank_id']);
    }

    public function testOpenAssessment()
    {
        $assessment = $this->mockAssessment();
        $assessment = $this->getAssessmentService()->openAssessment($assessment['id']);
        $this->assertEquals('open', $assessment['status']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     */
    public function testOpenAssessment_whenStatusOpen_thenThrowException()
    {
        $assessment = $this->mockAssessment(['status' => 'open']);
        $this->getAssessmentService()->openAssessment($assessment['id']);
    }

    public function testCloseAssessment()
    {
        $assessment = $this->mockAssessment(['status' => 'open']);
        $assessment = $this->getAssessmentService()->closeAssessment($assessment['id']);
        $this->assertEquals('closed', $assessment['status']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Assessment\Exception\AssessmentException
     */
    public function testCloseAssessment_whenStatusClosed_thenThrowException()
    {
        $assessment = $this->mockAssessment(['status' => 'closed']);
        $this->getAssessmentService()->closeAssessment($assessment['id']);
    }

    public function testReview()
    {
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $item = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ]);
        $secondItem = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ]);
        $response = [
            [
                'section_id' => $section['id'],
                'item_responses' => [
                    [
                        'item_id' => $item['item_id'],
                        'question_responses' => [['question_id' => $item['question_scores'][0]['question_id'], 'response' => ['rp1']]],
                    ],
                    [
                        'item_id' => $secondItem['item_id'],
                        'question_responses' => [['question_id' => $secondItem['question_scores'][0]['question_id'], 'response' => []]],
                    ],
                ],
            ],
        ];

        $result = $this->getAssessmentService()->review($assessment['id'], $response);

        $this->assertEquals(2, $result['score']);
        $this->assertEquals('right', $result['section_reports'][0]['item_reports'][0]['question_reports'][0]['status']);
        $this->assertEquals('no_answer', $result['section_reports'][0]['item_reports'][1]['question_reports'][0]['status']);
    }

    public function testExportAssessment()
    {
        $assessment = $this->mockCompleteAssessment();
        $result = $this->getAssessmentService()->exportAssessment($assessment['id'], '/tmp/test.docx', '');

        $this->assertTrue($result);
    }

    public function testFindAssessmentQuestions()
    {
        $this->mockObjectIntoBiz('ItemBank:Assessment:AssessmentSectionItemService', [
            [
                'functionName' => 'findSectionItemsByAssessmentId',
                'returnValue' => [
                    [
                        'item_id' => 1, 
                        'section_id' => 1, 
                        'question_scores' => [
                            ['question_id' => 1, 'score' => 2],
                            ['question_id' => 2, 'score' => 2],
                        ]
                    ],
                ],
            ]
        ]);

        $assessmentQuestions = $this->getAssessmentService()->findAssessmentQuestions($assessment['id']);

        $this->assertEquals(1, $assessmentQuestions['1']['question_id']);
        $this->assertEquals(2, $assessmentQuestions['1']['score']);
        $this->assertEquals(1, $assessmentQuestions['1']['section_id']);
        $this->assertEquals(1, $assessmentQuestions['1']['item_id']);
        $this->assertEquals(2, $assessmentQuestions['2']['question_id']);
        $this->assertEquals(2, $assessmentQuestions['2']['score']);
        $this->assertEquals(1, $assessmentQuestions['1']['section_id']);
        $this->assertEquals(1, $assessmentQuestions['1']['item_id']);
    }

    protected function mockCompleteAssessment()
    {
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ]);
        $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
            'item_id' => 0,
        ]);

        return $assessment;
    }

    protected function mockAssessment($assessment = [])
    {
        $default = [
            'bank_id' => 1,
            'name' => 'test',
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

    protected function mockSectionItem($sectionItem = [], $item = [])
    {
        $item = $this->mockItem($item);
        $default = [
            'assessment_id' => 1,
            'item_id' => $item['id'],
            'section_id' => 1,
            'seq' => 1,
            'score' => 5,
            'question_scores' => [['question_id' => $item['questions'][0]['id'], 'score' => 2]],
            'score_rule' => [
                [
                    'question_id' => $item['questions'][0]['id'],
                    'seq' => 1,
                    'rule' => [['name' => 'all_right', 'score' => 2], ['name' => 'part_right', 'score' => 1], ['name' => 'no_answer', 'score' => 0], ['name' => 'wrong', 'score' => 0]],
                ],
            ],
        ];
        $sectionItem = array_merge($default, $sectionItem);

        return $this->getAssessmentSectionItemDao()->create($sectionItem);
    }

    public function mockItem($item = [])
    {
        $default = [
            'bank_id' => 1,
            'category_id' => '1',
            'difficulty' => 'normal',
            'material' => '',
            'analysis' => '<p>这是解析</p>',
            'type' => 'single_choice',
            'question_num' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $item = array_merge($default, $item);
        $item = $this->getItemDao()->create($item);
        $question = [
            'item_id' => $item['id'],
            'stem' => '<p>这是题干</p>',
            'seq' => '1',
            'score' => '2.0',
            'answer' => ['rp1'],
            'analysis' => '<p>这是解析</p>',
            'answer_mode' => 'single_choice',
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'response_points' => [
                [
                    'radio' => [
                    'val' => 'rp1',
                    'text' => '<p>选项A</p>',
                    ],
                ],
                [
                    'radio' => [
                    'val' => 'rp2',
                    'text' => '<p>选项B</p>',
                    ],
                ],
                [
                    'radio' => [
                    'val' => 'rp3',
                    'text' => '<p>选项C</p>',
                    ],
                ],
                [
                    'radio' => [
                    'val' => 'rp4',
                    'text' => '<p>选项D</p>',
                    ],
                ],
            ],
        ];

        $question = $this->getQuestionDao()->create($question);
        $item['questions'][] = $question;

        return $item;
    }

    protected function mockItemBankService()
    {
        $this->mockObjectIntoBiz('ItemBank:ItemBank:ItemBankService', [
            [
                'functionName' => 'getItemBank',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'updateItemNum',
                'returnValue' => 1,
            ],
            [
                'functionName' => 'updateAssessmentNum',
                'returnValue' => 1,
            ],
        ]);
    }

    protected function mockItemCategoryService()
    {
        $this->mockObjectIntoBiz('ItemBank:Item:ItemCategoryService', [[
            'functionName' => 'getItemCategory',
            'functionName' => 'getItemCategory',
            'returnValue' => ['id' => 1],
        ]]);
    }

    /**
     * @return AssessmentDao
     */
    protected function getAssessmentDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentDao');
    }

    /**
     * @return AssessmentSectionDao
     */
    protected function getAssessmentSectionDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionDao');
    }

    /**
     * @return AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionItemDao');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }
}
