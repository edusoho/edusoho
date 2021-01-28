<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Exception\QuestionException;

class TextAnswerMode extends AnswerMode
{
    const NAME = 'text';

    const INPUT_TYPE = 'text';

    const IS_SUBJECTIVE = false;

    public function validate($responsePoints, $answer)
    {
        parent::validate($responsePoints, $answer);

        if (count($responsePoints) != count($answer)) {
            throw new QuestionException('Field response_points or answer is invalid.', ErrorCode::QUESTION_ARGUMENT_INVALID);
        }
    }

    public function getAnswerSceneResponsePointsReport($question, $questionReports)
    {
        $reports = [];
        foreach ($question['answer'] as $answer) {
            $reports[] = ['val' => $answer, 'num' => 0];
        }

        foreach ($questionReports as $questionReport) {
            foreach ($questionReport['response'] as $index => $response) {
                if (!empty($question['answer'][$index])) {
                    $pointAnswers = explode('|', $question['answer'][$index]);
                }
                if (in_array($response, $pointAnswers)) {
                    ++$reports[$index]['num'];
                }
            }
        }

        return $reports;
    }

    public function parse($parsedQuestion, $question)
    {
        foreach ($question['answers'] as $answer) {
            $parsedQuestion['response_points'][] = [self::INPUT_TYPE => []];
        }

        $parsedQuestion['answer'] = $question['answers'];

        return $parsedQuestion;
    }

    protected function reviewPoint($key, $responsePoint, $answer, $response)
    {
        foreach ($answer as &$pointAnswer) {
            $pointAnswer = explode('|', $pointAnswer);
            unset($pointAnswer);
        }

        if (empty($response[$key])) {
            return self::POINT_NOANSWER;
        }

        return in_array($response[$key], $answer[$key]) ? self::POINT_RIGHT : self::POINT_WRONG;
    }
}
