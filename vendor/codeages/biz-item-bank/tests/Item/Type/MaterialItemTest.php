<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\MaterialItem;
use Tests\IntegrationTestCase;

class MaterialItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertTrue($this->getMaterialItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_MaterialEmpty_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_QuestionNumLessThanMin_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_QuestionNumMoreThanMax_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[], []],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_AnswerModeEmpty_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_AnswerModeNotAllow_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'match']
            ],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     */
    public function testValidate_BankNotExist_ThrowException()
    {
        $this->getMaterialItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'material',
            'material' => '材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'rich_text']
            ],
        ]);
    }

    public function testProcess()
    {
        $this->mockItemBankService();

        $item = $this->getMaterialItemProcessor()->process([
            'bank_id' => '1',
            'category_id' => '',
            'difficulty' => 'normal',
            'material' => '材料',
            'analysis' => '<p>这是解析</p>',
            'type' => 'material',
            'questions' => [
                [
                    'stem' => '<p>这是题干</p>',
                    'seq' => '1',
                    'score' => '2.0',
                    'response_points' => [['rich_text' => []]],
                    'answer' => [],
                    'analysis' => '<p>这是解析</p>',
                    'answer_mode' => 'rich_text',
                ],
            ],
        ]);

        $this->assertEquals(1, $item['question_num']);
        $this->assertFalse(isset($item['category_id']));
    }

    protected function getMaterialItemProcessor()
    {
        return new MaterialItem($this->biz);
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
        ]);
    }
}
