<?php
namespace Topxia\Service\Question\Type;

class MaterialQuestionType extends AbstractQuestionType
{
    public function judge(array $question, $answer)
    {
        return array('status' => 'none');
    }

    public function canHaveSubQuestion()
    {
        return true;
    }

}