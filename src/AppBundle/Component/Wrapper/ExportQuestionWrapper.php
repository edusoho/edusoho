<?php

namespace AppBundle\Component\Wrapper;

class ExportQuestionWrapper extends Wrapper
{
    public function seq($question)
    {
        return $question;
    }

    public function stem($question)
    {
        return $question;
    }

    public function options($question)
    {
        $question['options'] = empty($question['metas']['choices']) ? array() : $question['metas']['choices'];

        return $question;
    }

    public function answer($question)
    {
        $question['answer'] = implode($question['answer']);

        return $question;
    }

    public function difficulty($question)
    {
        $difficulties = array(
            'simple' => '简单',
            'normal' => '一般',
            'difficulty' => '困难',
        );

        $question['difficulty'] = $difficulties[$question['difficulty']];

        return $question;
    }

    protected function getWrapList()
    {
        return array(
            'seq',
            'stem',
            'options',
            'answer',
            'difficulty'
        );
    }
}
