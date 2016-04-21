<?php
namespace Topxia\Service\Question\Type;

class FillQuestionType extends AbstractQuestionType
{
    public function judge(array $question, $answer)
    {
        return array('status' => 'none');
    }

}
