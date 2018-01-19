<?php

namespace Biz\Question\Type;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class Fill extends BaseQuestion implements TypeInterface
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
        $questionAnswers = array_values($question['answer']);
        $answer = array_values($answer);

        if (count($answer) != count($questionAnswers)) {
            return array('status' => 'wrong', 'score' => 0);
        }

        $userAnswerIndex = $this->getAnswerIndex($questionAnswers, $answer);
        $rightCount = count($userAnswerIndex);

        if (0 == $rightCount) {
            return array('status' => 'wrong', 'score' => 0);
        } elseif ($rightCount < count($questionAnswers)) {
            $percentage = intval($rightCount / count($questionAnswers) * 100);
            $score = ($question['score'] * $percentage) / 100;
            $score = number_format($score, 1, '.', '');

            return array('status' => 'partRight', 'percentage' => $percentage, 'score' => $score);
        } else {
            return array('status' => 'right', 'score' => $question['score']);
        }
    }

    public function filter(array $fields)
    {
        $fields = parent::filter($fields);

        preg_match_all("/\[\[(.+?)\]\]/", $fields['stem'], $answer, PREG_PATTERN_ORDER);
        if (empty($answer[1])) {
            throw new InvalidArgumentException('This Question Answer Unexpected');
        }

        $fields['answer'] = array();
        foreach ($answer[1] as $value) {
            $value = explode('|', $value);
            foreach ($value as $i => $v) {
                $value[$i] = trim($v);
            }
            $fields['answer'][] = $value;
        }

        return $fields;
    }

    public function getAnswerStructure($question)
    {
        return $question['answer'];
    }

    public function analysisAnswerIndex($question, $userAnswer)
    {
        $questionAnswers = array_values($question['answer']);
        $answer = array_values($userAnswer['answer']);

        $userRightAnswerIndex = $this->getAnswerIndex($questionAnswers, $answer);

        return array($question['id'] => $userRightAnswerIndex);
    }

    protected function getAnswerIndex($questionAnswers, $answer)
    {
        $userRightAnswerIndex = array();
        foreach ($questionAnswers as $index => $rightAnswer) {
            $expectAnswer = array();
            foreach ($rightAnswer as $key => $value) {
                $value = trim($value);
                $value = preg_replace("/([\x20\s\t]){2,}/", ' ', $value);
                $expectAnswer[] = $value;
            }

            $actualAnswer = trim($answer[$index]);
            $actualAnswer = preg_replace("/([\x20\s\t]){2,}/", ' ', $actualAnswer);
            if (in_array($actualAnswer, $expectAnswer)) {
                $userRightAnswerIndex[] = $index;
            }
        }

        return $userRightAnswerIndex;
    }
}
