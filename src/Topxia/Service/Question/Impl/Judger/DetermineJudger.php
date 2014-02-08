<?php
namespace Topxia\Service\Question\Impl\Judger;

class DetermineJudger implements Judger
{
    public function judge(array $question, $answer)
    {
        $rightAnswer = array_pop($question['answer']);
        $userAnswer = array_pop($answer);

        $status = $userAnswer == $rightAnswer ? 'right' : 'wrong';

        return array('status' => $status);
    }
}