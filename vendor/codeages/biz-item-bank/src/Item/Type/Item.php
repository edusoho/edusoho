<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Util\Validator\Validator;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

abstract class Item
{
    protected $biz;

    protected $allowMaxQuestionNum;

    protected $allowMinQuestionNum;

    protected $allowAnswerModes;

    protected $allowMaterials;

    public function __construct($biz)
    {
        $this->biz = $biz;

        $this->setAllowMinQuestionNum();
        $this->setAllowMaxQuestionNum();
        $this->setAllowAnswerModes();
        $this->setAllowMaterials();
    }

    abstract public function setAllowMinQuestionNum();

    abstract public function setAllowMaxQuestionNum();

    abstract public function setAllowAnswerModes();

    abstract public function setAllowMaterials();

    public function isAllowMaterials()
    {
        return $this->allowMaterials;
    }

    public function validate($item)
    {
        $item = $this->getValidator()->validate($item, [
            'bank_id' => ['integer'],
            'type' => ['required'],
            'material' => [['required', !$this->allowMaterials]],
            'analysis' => [['required', true]],
            'category_id' => ['integer'],
            'difficulty' => ['required'],
            'questions' => ['array'],
        ]);

        if (static::TYPE != $item['type']) {
            throw new ItemException('Not match type of item', ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        if (count($item['questions']) < $this->allowMinQuestionNum) {
            throw new ItemException("This item require {$this->allowMinQuestionNum} question at least.", ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        if (count($item['questions']) > $this->allowMaxQuestionNum) {
            throw new ItemException("This item can only have {$this->allowMaxQuestionNum} questions at most.", ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        if (!$this->allowMaterials && !empty($item['material'])) {
            throw new ItemException('This item do not allow material', ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        $answerModes = array_column($item['questions'], 'answer_mode');
        if (empty($answerModes)) {
            throw new ItemException('The questions don not have a answer mode.', ErrorCode::ITEM_ARGUMENT_INVALID);
        }
        if (array_diff($answerModes, $this->allowAnswerModes)) {
            throw new ItemException('The answer mode of question is not allowed.', ErrorCode::ITEM_ARGUMENT_INVALID);
        }

        if (empty($this->getItemBankService()->getItemBank($item['bank_id']))) {
            throw new ItemBankException('Item bank not found', ErrorCode::ITEM_BANK_NOT_FOUND);
        }

        return $item;
    }

    public function process($item)
    {
        $item = $this->validate($item);

        $item['analysis'] = $this->purifyHtml($item['analysis']);
        $item['question_num'] = count($item['questions']);
        $item['material'] = $this->purifyHtml($this->getMaterial($item));
        $item['material'] = preg_replace('/\[\[.+?\]\]/', '[[]]', $item['material']);
        if (empty($item['category_id'])) {
            unset($item['category_id']);
        }

        $seq = 1;
        foreach ($item['questions'] as &$question) {
            $question['seq'] = $seq++;
            $question = $this->getQuestionProcessor()->process($question);
            unset($question);
        }

        return $item;
    }

    public function review($itemId, $questionResponses)
    {
        $item = $this->getItemService()->getItemWithQuestions($itemId);
        if (empty($item)) {
            return $this->getDeleteItemReviewResult($itemId, $questionResponses);
        }

        $questionReviewResult = [];
        $questionResponses = ArrayToolkit::index($questionResponses, 'question_id');
        foreach ($questionResponses as $questionId => $questionResponse) {
            $questionReviewResult[] = $this->getQuestionProcessor()->review($questionId, empty($questionResponse['response']) ? [] : $questionResponse['response']);
        }

        return [
            'item_id' => $itemId,
            'result' => $this->getItemReviewResult($questionReviewResult),
            'question_responses_review_result' => $questionReviewResult,
        ];
    }

    public function getDeleteItemReviewResult($itemId, $questionResponses)
    {
        $reviewResult = [
            'item_id' => $itemId,
            'result' => Question::REVIEW_WRONG,
            'question_responses_review_result' => [],
        ];

        foreach ($questionResponses as $questionResponse) {
            $reviewResult['question_responses_review_result'][] = $this->getQuestionProcessor()->getDeleteQuestionReviewResult(
                $questionResponse['question_id'],
                $questionResponse['response']
            );
        }

        return $reviewResult;
    }

    protected function getMaterial($item)
    {
        return $item['questions'][0]['stem'];
    }

    protected function getItemReviewResult($questionReviewResult)
    {
        $questionResults = array_column($questionReviewResult, 'result');
        if (in_array('wrong', $questionResults)) {
            return 'wrong';
        }
        if (['right'] == array_unique($questionResults)) {
            return 'right';
        }

        return 'none';
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->biz->service('ItemBank:ItemBank:ItemBankService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return Question
     */
    protected function getQuestionProcessor()
    {
        return $this->biz['question_processor'];
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->biz['validator'];
    }

    protected function purifyHtml($html)
    {
        return $this->biz['item_bank_html_helper']->purify($html);
    }
}
