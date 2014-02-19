<?php
namespace Topxia\Service\Question\Type;

class FillQuestionType extends AbstractQuestionType
{

    public function filter($fields, $mode = 'create')
    {
        $fields = $this->commonFilter($fields, $mode);

        preg_match_all("/\[\[(.+?)\]\]/", $fields['stem'], $answer, PREG_PATTERN_ORDER);
        if (empty($answer[1])){
            throw $this->createServiceException('该问题没有答案或答案格式不正确！');
        }

        $fields['answer'] = array();
        foreach ($answer[1] as $value) {
            $value = explode('|', $value);
            foreach ($value as $i => $v) {
                $value[$i] = trim($v);
            }
            $fields['answer'][] = $value;
        }

        return $fields;
    }

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