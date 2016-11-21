<?php

namespace Biz\FillQuestion;

use Biz\Question\Config\Question;
use Topxia\Common\Exception\UnexpectedValueException;

class FillQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '填空题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:FillQuestion:create',
            'edit'   => 'WebBundle:FillQuestion:edit',
            'show'   => 'WebBundle:FillQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:FillQuestion:form.html.twig',
            'edit'   => 'WebBundle:FillQuestion:form.html.twig',
            'do'     => 'WebBundle:FillQuestion:do.html.twig'
        );
    }

    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

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
        $questionAnswers = array_values($question['answer']);
        $answer          = array_values($answer);

        if (count($answer) != count($questionAnswers)) {
            return array('status' => 'wrong', 'score' => 0);
        }

        $rightCount = 0;
        foreach ($questionAnswers as $index => $rightAnswer) {
            $expectAnswer = array();
            foreach ($rightAnswer as $key => $value) {
                $value          = trim($value);
                $value          = preg_replace("/([\x20\s\t]){2,}/", " ", $value);
                $expectAnswer[] = $value;
            }

            $actualAnswer = trim($answer[$index]);
            $actualAnswer = preg_replace("/([\x20\s\t]){2,}/", " ", $actualAnswer);
            if (in_array($actualAnswer, $expectAnswer)) {
                $rightCount++;
            }
        }

        if ($rightCount == 0) {
            return array('status' => 'wrong', 'score' => 0);
        } elseif ($rightCount < count($questionAnswers)) {
            $percentage = intval($rightCount / count($questionAnswers) * 100);
            $score      = ($question['score'] * $percentage) / 100;
            $score      = number_format($score, 1, '.', '');
            return array('status' => 'partRight', 'percentage' => $percentage, 'score' => $score);
        } else {
            return array('status' => 'right', 'score' => $question['score']);
        }
    }

    public function filter($fields, $mode)
    {
        $fields = parent::commonFilter($fields, $mode);

        preg_match_all("/\[\[(.+?)\]\]/", $fields['stem'], $answer, PREG_PATTERN_ORDER);
        if (empty($answer[1])) {
            throw new UnexpectedValueException("This Question Answer Unexpected");
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
}
