<?php
namespace Topxia\Service\Question\Type;

class EssayQuestionType extends AbstractQuestionType
{
    public function filter($fields, $mode = 'create')
    {
        $fields = $this->commonFilter($fields, $mode);

        if (!empty($fields['answer']) && is_array($fields['answer'])) {
            foreach($fields['answer'] as &$answer) {
                $answer = $this->purifyHtml($answer);
                unset($answer);
            }
        }

        return $fields;
    }

    public function judge(array $question, $answer)
    {
        return array('status' => 'none');
    }

}