<?php

namespace Biz\Question\Type;

class Material extends BaseQuestion implements TypeInterface
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

    public function judge($question, $answer)
    {
        return array('status' => 'none', 'score' => 0);
    }

    public function getAnswerStructure($question)
    {
        return array();
    }

    public function analysisAnswerIndex($question, $userAnswer)
    {
        return array();
    }
}
