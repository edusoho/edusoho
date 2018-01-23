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

    public function getAnswerStructure($question)
    {
        //问答题0：0分; 1:0到满分之间; 2：满分
        return array(0, 1, 2);
    }

    public function analysisAnswerIndex($question, $userAnswer)
    {
        if ($userAnswer['score'] == $question['score']) {
            $answerIndex = 2;
        } elseif ($userAnswer['score'] > 0 && $userAnswer['score'] < $question['score']) {
            $answerIndex = 1;
        } else {
            $answerIndex = 0;
        }

        return array($question['id'] => array($answerIndex));
    }
}
