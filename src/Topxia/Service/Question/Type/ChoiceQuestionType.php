<?php
namespace Topxia\Service\Question\Type;

class ChoiceQuestionType extends AbstractQuestionType
{

    public function hasMissScore()
    {
        return true;
    }

    public function filter($fields, $mode = 'create')
    {
        if (empty($fields['uncertain'])) {
            $fields['type'] = count($fields['answer']) == 1 ? 'single_choice' : 'choice';
        } else {
            $fields['type'] = 'uncertain_choice';
        }

        $fields['metas'] = array('choices' => $fields['choices']);
        return $this->commonFilter($fields, $mode);
    }

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