<?php
namespace Topxia\Service\Quiz\Impl;

class QuestionSerialize
{
    public static function serialize(array $question)
    {
        if (isset($question['answer'])) {
            $question['answer'] = json_encode($question['answer']);
        }
        return $question;
    }

    public static function unserialize(array $question = null)
    {
        if (empty($question)) {
            return null;
        }
        if(!empty($question['answer'])){
            $question['answer'] = json_decode($question['answer'],true);
        }
        return $question;
    }

    public static function unserializes(array $questions)
    {
        return array_map(function($question) {
            return QuestionSerialize::unserialize($question);
        }, $questions);
    }
}