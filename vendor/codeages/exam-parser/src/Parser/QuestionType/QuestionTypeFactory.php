<?php

namespace ExamParser\Parser\QuestionType;

class QuestionTypeFactory
{
    public static function create($type)
    {
        $class = '\\ExamParser\\Parser\\QuestionType\\'.$type;

        return new $class();
    }
}
