<?php

namespace Tests\Assessment\Util;

use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentDao;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionDao;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Tests\IntegrationTestCase;

class AssessmentReviewTest extends IntegrationTestCase
{
    public function testReview()
    {
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $item = $this->mockItem();
        $secondItem = $this->mockItem();
        $thirdItem = $this->mockItem();
        $fourthItem = $this->mockItem(['type' => 'essay'], ['answer_mode' => 'rich_text']);
        $sectionItem = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ], $item);
        $secondSectionItem = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ], $secondItem);
        $thirdSectionItem = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ], $thirdItem);
        $fourthSectionItem = $this->mockSectionItem([
            'section_id' => $section['id'],
            'assessment_id' => $assessment['id'],
        ], $fourthItem);
        $response = [
            [
                'section_id' => $section['id'],
                'item_responses' => [
                    [
                        'item_id' => $sectionItem['item_id'],
                        'question_responses' => [['question_id' => $sectionItem['question_scores'][0]['question_id'], 'response' => ['rp1']]],
                    ],
                    [
                        'item_id' => $secondSectionItem['item_id'],
                        'question_responses' => [['question_id' => $secondSectionItem['question_scores'][0]['question_id'], 'response' => []]],
                    ],
                    [
                        'item_id' => $thirdSectionItem['item_id'],
                        'question_responses' => [['question_id' => $thirdSectionItem['question_scores'][0]['question_id'], 'response' => ['rp2']]],
                    ],
                    [
                        'item_id' => $fourthSectionItem['item_id'],
                        'question_responses' => [['question_id' => $fourthSectionItem['question_scores'][0]['question_id'], 'response' => ['answer']]],
                    ],
                ],
            ],
        ];

        $result = $this->getAssessmentReview()->review($assessment, $response);

        $this->assertEquals(3, $result['score']);
        $this->assertEquals('right', $result['section_reports'][0]['item_reports'][0]['question_reports'][0]['status']);
        $this->assertEquals('no_answer', $result['section_reports'][0]['item_reports'][1]['question_reports'][0]['status']);
        $this->assertEquals('wrong', $result['section_reports'][0]['item_reports'][2]['question_reports'][0]['status']);
        $this->assertEquals('reviewing', $result['section_reports'][0]['item_reports'][3]['question_reports'][0]['status']);
    }

    public function testReview_whenItemNotExist_returnNoAnswer()
    {
        $assessment = $this->mockAssessment();
        $section = $this->mockSection(['assessment_id' => $assessment['id']]);
        $item = $this->mockSectionItem([
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
                ],
            ],
        ];

        $assessReport = $this->getAssessmentReview()->review($assessment, $response);
        $this->assertEquals('no_answer', $assessReport['section_reports'][0]['item_reports'][0]['question_reports'][0]['status']);
    }

    public function testReview_whenSectionEmpty_thenReturnEmptySectionResult()
    {
        $assessment = $this->mockAssessment();
        $response = [
            [
                'section_id' => 1,
                'item_responses' => [
                    [
                        'item_id' => 1,
                        'question_responses' => [['question_id' => 1, 'response' => ['rp1']]],
                    ],
                ],
            ],
        ];

        $result = $this->getAssessmentReview()->review($assessment, $response);

        $this->assertEmpty($result['section_reports'][0]['item_reports']);
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
            'total_score' => 3,
        ];
        $section = array_merge($default, $section);

        return $this->getAssessmentSectionDao()->create($section);
    }

    protected function mockSectionItem($sectionItem = [], $item = [])
    {
        $defaultItem = [
            'id' => 0,
            'questions' => [['id' => 0]],
        ];
        $item = array_merge($defaultItem, $item);
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
                    'rule' => [['name' => 'all_right', 'score' => 3], ['name' => 'no_answer', 'score' => 0], ['name' => 'wrong', 'score' => 0]],
                ],
            ],
        ];
        $sectionItem = array_merge($default, $sectionItem);

        return $this->getAssessmentSectionItemDao()->create($sectionItem);
    }

    public function mockItem($item = [], $question = [])
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
        $defaultQuestion = [
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

        $question = array_merge($defaultQuestion, $question);
        $question = $this->getQuestionDao()->create($question);
        $item['questions'][] = $question;

        return $item;
    }

    protected function getAssessmentReview()
    {
        return $this->biz['assessment_review_helper'];
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
