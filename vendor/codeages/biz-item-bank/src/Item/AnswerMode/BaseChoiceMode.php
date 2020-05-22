<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

abstract class BaseChoiceMode extends AnswerMode
{
    const ASCLL_NUM = 65;

    const IS_SUBJECTIVE = false;

    public function validate($responsePoints, $answer)
    {
        parent::validate($responsePoints, $answer);

        $responsePoints = array_column($responsePoints, static::INPUT_TYPE);
        if (count($responsePoints) < 2) {
            throw new QuestionException('At least 2 options.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }

        $this->validateQuestionCount($answer);

        if (array_diff($answer, array_column($responsePoints, 'val'))) {
            throw new QuestionException('Field response_points or answer is invalid.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
        foreach ($responsePoints as $responsePoint) {
            $this->getValidator()->validate($responsePoint, [
                'val' => ['required'],
                'text' => ['required'],
            ]);
        }
    }

    public function getAnswerSceneResponsePointsReport($question, $questionReports)
    {
        $reports = [];
        foreach ($question['response_points'] as $responsePoint) {
            $val = $responsePoint[static::INPUT_TYPE]['val'];
            $reports[$val] = ['val' => $val, 'num' => 0];
        }

        foreach ($questionReports as $questionReport) {
            foreach ($questionReport['response'] as $response) {
                isset($reports[$response]) && $reports[$response]['num']++;
            }
        }

        return array_values($reports);
    }

    public function filter($responsePoints)
    {
        foreach ($responsePoints as &$responsePoint) {
            $responsePoint[static::INPUT_TYPE]['text'] = $this->purifyHtml($responsePoint[static::INPUT_TYPE]['text']);
            $responsePoint[static::INPUT_TYPE] = ArrayToolkit::parts($responsePoint[static::INPUT_TYPE], ['val', 'text']);
            unset($responsePoint);
        }

        return $responsePoints;
    }

    public function parse($parsedQuestion, $question)
    {
        $parsedQuestion['response_points'] = [];
        $ascll = self::ASCLL_NUM;
        foreach ($question['options'] as $option) {
            $responsePoint[static::INPUT_TYPE]['text'] = $option;
            $responsePoint[static::INPUT_TYPE]['val'] = chr($ascll);
            $parsedQuestion['response_points'][] = $responsePoint;
            ++$ascll;
        }

        foreach ($question['answers'] as $answer) {
            $parsedQuestion['answer'][] = $parsedQuestion['response_points'][$answer][static::INPUT_TYPE]['val'];
        }

        return $parsedQuestion;
    }

    protected function reviewPoint($key, $responsePoint, $answer, $response)
    {
        if (in_array($responsePoint['val'], $response)) {
            return in_array($responsePoint['val'], $answer) ? static::POINT_RIGHT : static::POINT_WRONG;
        }

        return self::POINT_NOANSWER;
    }

    abstract protected function validateQuestionCount($answer);
}
