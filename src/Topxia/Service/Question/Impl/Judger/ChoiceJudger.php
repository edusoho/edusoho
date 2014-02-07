<?php
namespace Topxia\Service\Question\Impl\Judger;

class ChoiceJudger implements Judger
{
    public function judge(array $question, $answer)
    {
        $questionAnswer = sort(array_values($questionAnswer));
        $userAnswer = sort(array_values($answer));

        if ($userAnswer == $questionAnswer) {
            return array('status' => 'right');
        }

    }
}