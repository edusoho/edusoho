<?php
namespace Topxia\Service\Question\Type;

class QuestionTypeFactory
{
    private static $cached = array();

    public static function create($type)
    {
        if (empty(self::$cached[$type])) {
            $type = ucfirst(str_replace('_', '', $type));
            $class = __NAMESPACE__  . "\\{$type}QuestionType";
            self::$cached[$type] = new $class();
        }

        return self::$cached[$type];
    }
}