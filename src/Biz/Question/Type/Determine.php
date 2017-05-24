<?php

namespace Biz\Question\Type;

class Determine extends BaseQuestion implements TypeInterface
{
    public function create($fields)
    {
    }

    public function update($id, $fields)
    {
    }

    public function delete($id)
    {
    }

    public function get($id)
    {
    }

    public function judge($question, $answer)
    {
        $rightAnswer = array_pop($question['answer']);
        $userAnswer = array_pop($answer);

        $status = $userAnswer == $rightAnswer ? 'right' : 'wrong';
        $score = $userAnswer == $rightAnswer ? $question['score'] : 0;

        return array('status' => $status, 'score' => $score);
    }
}
