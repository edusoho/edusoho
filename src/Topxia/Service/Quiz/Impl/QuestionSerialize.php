<?php
namespace Topxia\Service\Quiz\Impl;

class QuestionSerialize
{
    public static function serialize(array $question)
    {
        if (isset($question['answer'])) {
            $question['answer'] = !is_array($question['answer']) ? array() : $question['answer'];
            $question['answer'] = json_encode($question['answer']);
        }

        if (isset($question['metas'])) {
            $question['metas'] = !is_array($question['metas']) ? array() : $question['metas'];
            $question['metas'] = json_encode($question['metas']);
        }

        return $question;
    }

    public static function unserialize(array $question = null)
    {
        if (empty($question)) {
            return null;
        }

        $question['answer'] = !empty($question['answer']) ? json_decode($question['answer'], true) : array();
        $question['metas'] = !empty($question['metas']) ? json_decode($question['metas'], true) : array();

        return $question;
    }

    public static function unserializes(array $questions)
    {
        return array_map(function($question) {
            return QuestionSerialize::unserialize($question);
        }, $questions);
    }
}