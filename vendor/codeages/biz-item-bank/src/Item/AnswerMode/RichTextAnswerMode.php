<?php

namespace Codeages\Biz\ItemBank\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\Type\Question;

class RichTextAnswerMode extends AnswerMode
{
    const NAME = 'rich_text';

    const INPUT_TYPE = 'rich_text';

    const IS_SUBJECTIVE = true;

    public function review($responsePoints, $answer, $response)
    {
        return [
            'result' => Question::NO_REVIEW,
            'response_points_result' => [],
        ];
    }

    public function parse($parsedQuestion, $question)
    {
        $parsedQuestion['response_points'] = [[self::INPUT_TYPE => []]];

        $parsedQuestion['answer'] = [$question['answer']];

        return $parsedQuestion;
    }

    public function getAnswerSceneResponsePointsReport($responsePoints, $questionReports)
    {
        return [];
    }

    protected function reviewPoint($key, $responsePoint, $answer, $response)
    {
    }
}
