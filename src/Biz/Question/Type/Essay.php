<?php

namespace Biz\Question\Type;

class Essay extends BaseQuestion implements TypeInterface
{
    public function create($fields)
    {
    }

    public function update($targetId, $fields)
    {
    }

    public function delete($targetId)
    {
    }

    public function get($targetId)
    {
    }

    public function filter(array $fields)
    {
        $fields = parent::filter($fields);
        if (!empty($fields['answer']) && is_array($fields['answer'])) {
            foreach ($fields['answer'] as &$answer) {
                $answer = $this->biz['html_helper']->purify($answer);
                unset($answer);
            }
        }

        return $fields;
    }

    public function judge($question, $answer)
    {
        return array('status' => 'none', 'score' => 0);
    }
}
