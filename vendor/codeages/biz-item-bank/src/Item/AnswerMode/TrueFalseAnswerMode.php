<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class TrueFalseAnswerMode extends AnswerMode
{
    const NAME = 'true_false';

    const INPUT_TYPE = 'radio';

    const IS_SUBJECTIVE = false;

    protected $truePoint = [
        'val' => 'T',
        'text' => '正确',
    ];

    protected $falsePoint = [
        'val' => 'F',
        'text' => '错误',
    ];

    public function validate($responsePoints, $answer)
    {
        parent::validate($responsePoints, $answer);

        $responsePoints = array_column($responsePoints, self::INPUT_TYPE);
        if (2 != count($responsePoints)) {
            throw new QuestionException('Determine question only has TRUE and FALSE options.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
        if (1 != count($answer)) {
            throw new QuestionException('Determine question only has one answer.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
        if (array_diff($answer, array_column($responsePoints, 'val'))) {
            throw new QuestionException('Field response_points or answer invalid', ErrorCode::QUESTION_ARGUMENT_INVALID);
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

    public function parse($parsedQuestion, $question)
    {
        $parsedQuestion['response_points'] = [
            [
                self::INPUT_TYPE => $this->truePoint,
            ],
            [
                self::INPUT_TYPE => $this->falsePoint,
            ],
        ];

        if ($question['answer']) {
            $parsedQuestion['answer'] = [$this->truePoint['val']];
        } else {
            $parsedQuestion['answer'] = [$this->falsePoint['val']];
        }

        return $parsedQuestion;
    }

    protected function reviewPoint($key, $responsePoint, $answer, $response)
    {
        if (in_array($responsePoint['val'], $response)) {
            return in_array($responsePoint['val'], $answer) ? self::POINT_RIGHT : self::POINT_WRONG;
        }

        return self::POINT_NOANSWER;
    }
}
