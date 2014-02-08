<?php
namespace Topxia\Service\Question\Impl\Judger;

class ChoiceJudger implements Judger
{
    public function judge(array $question, $answer)
    {
        if (count(array_diff($question['answer'], $answer)) == 0 && count(array_diff($answer, $question['answer'])) == 0) {
            return array('status' => 'right');
        }

        if (count(array_diff($answer, $question['answer'])) == 0) {
            $percentage = intval(count($answer) / count($question['answer']) * 100);
            return array('status' => 'partRight', 'percentage' => $percentage);
        }

        return array('status' => 'wrong');
    }
}