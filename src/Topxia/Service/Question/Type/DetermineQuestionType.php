<?php
namespace Topxia\Service\Question\Type;

class DetermineQuestionType extends AbstractQuestionType
{
    public function judge(array $question, $answer)
    {
        $rightAnswer = array_pop($question['answer']);
        $userAnswer = array_pop($answer);

        $status = $userAnswer == $rightAnswer ? 'right' : 'wrong';

        return array('status' => $status);
    }

}