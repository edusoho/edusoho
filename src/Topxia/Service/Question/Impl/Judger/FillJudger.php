<?php
namespace Topxia\Service\Question\Impl\Judger;

class FillJudger implements Judger
{
    public function judge(array $question, $answer)
    {
        $questionAnswers = array_values($question['answer']);
        $answer = array_values($answer);

        if (count($answer) != count($questionAnswers)) {
            return array('status' => 'wrong');
        }

        $rightCount = 0;
        foreach ($questionAnswers as $index => $rightAnswer) {
            if (in_array($answer[$index], $rightAnswer)) {
                $rightCount++;
            }
        }

        if ($rightCount == 0) {
            return array('status' => 'wrong');
        } elseif ($rightCount < count($questionAnswers)) {
            $percentage = intval($rightCount / count($questionAnswers) * 100);
            return array('status' => 'partRight', 'percentage' => $percentage);
        } else {
            return array('status' => 'right');
        }
    }
}