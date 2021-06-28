<?php

namespace Tests\Unit\WrongBook\Service;

use Biz\BaseTestCase;
use Biz\WrongBook\Service\WrongBookAssessmentService;

class WrongBookAssessmentServiceTest extends BaseTestCase
{
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

        $assessment = $this->getWrongBookAssessmentService()->createAssessment($assessment);

        $this->assertEquals('testSection', $assessment['sections'][0]['name']);
        $this->assertEquals(1, $assessment['sections'][0]['seq']);
        $this->assertEquals(1, $assessment['sections'][0]['items'][0]['seq']);
        $this->assertEquals('testAssessment', $assessment['name']);
    }

    protected function mockItemBankService()
    {
        $this->mockBiz('ItemBank:ItemBank:ItemBankService', [
            [
                'functionName' => 'getItemBank',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'updateItemNumAndQuestionNum',
                'returnValue' => 1,
            ],
            [
                'functionName' => 'updateAssessmentNum',
                'returnValue' => 1,
            ],
        ]);
    }

    /**
     * @return WrongBookAssessmentService
     */
    protected function getWrongBookAssessmentService()
    {
        return $this->getBiz()->service('WrongBook:WrongBookAssessmentService');
    }
}
