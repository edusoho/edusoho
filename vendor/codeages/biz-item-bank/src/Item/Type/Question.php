<?php

namespace Codeages\Biz\ItemBank\Item\Type;

use Codeages\Biz\ItemBank\Item\AnswerMode\AnswerMode;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;

class Question
{
    const REVIEW_RIGHT = 'right';

    const REVIEW_WRONG = 'wrong';

    const NO_REVIEW = 'none';

    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function validate($question)
    {
        $question = $this->biz['validator']->validate($question, [
            'id' => [['min', 0]],
            'stem' => ['required'],
            'seq' => ['integer'],
            'score' => [['regex', '/^\d+(\.\d)?$/']],
            'analysis' => [['required', true]],
            'answer' => ['array'],
            'response_points' => ['array'],
            'answer_mode' => ['required'],
            'attachments' => ['array'],
        ]);

        $this->getAnswerMode($question['answer_mode'])->validate($question['response_points'], $question['answer']);

        return $question;
    }

    public function process($question)
    {
        $question = $this->validate($question);

        if (isset($question['id']) && empty($question['id'])) {
            unset($question['id']);
        }

        $question['stem'] = $this->purifyHtml($question['stem']);
        $question['stem'] = preg_replace('/\[\[.+?\]\]/', '[[]]', $question['stem']);
        $question['analysis'] = $this->purifyHtml($question['analysis']);
        $question['response_points'] = $this->getAnswerMode($question['answer_mode'])->filter($question['response_points']);

        return $question;
    }

    public function review($questionId, $response)
    {
        $question = $this->getQuestionDao()->get($questionId);
        if (empty($question)) {
            return $this->getDeleteQuestionReviewResult($questionId, $response);
        }

        $reviewResult = $this->getAnswerMode($question['answer_mode'])->review(
            $question['response_points'],
            $question['answer'],
            $response
        );

        return [
            'question_id' => $questionId,
            'result' => $reviewResult['result'],
            'response_points_result' => $reviewResult['response_points_result'],
            'response' => $response,
        ];
    }

    public function getDeleteQuestionReviewResult($questionId, $response)
    {
        $reviewResult = [
            'question_id' => $questionId,
            'result' => Question::REVIEW_WRONG,
            'response_points_result' => [],
            'response' => [],
        ];

        foreach ($response as $res) {
            $reviewResult['response_points_result'][] = Question::NO_REVIEW;
            $reviewResult['response'][] = '';
        }

        return $reviewResult;
    }

    /**
     * @param $answerMode
     *
     * @return AnswerMode
     */
    protected function getAnswerMode($answerMode)
    {
        return $this->biz['answer_mode_factory']->create($answerMode);
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }

    protected function purifyHtml($html)
    {
        return $this->biz['item_bank_html_helper']->purify($html);
    }
}
