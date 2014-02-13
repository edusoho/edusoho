<?php
namespace Topxia\Service\Question\Type;

class MaterialQuestionType extends AbstractQuestionType
{
    public function canHaveSubQuestion()
    {
        return true;
    }
}