<?php

namespace Biz\UncertainChoiceQuesiton;

use Biz\Question\Config\Question;

class UncertainChoiceQuesiton extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '不定项选择题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:UncertainChoiceQuesiton:create',
            'edit'   => 'WebBundle:UncertainChoiceQuesiton:edit',
            'show'   => 'WebBundle:UncertainChoiceQuesiton:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:UncertainChoiceQuesiton:form.html.twig',
            'edit'   => 'WebBundle:UncertainChoiceQuesiton:form.html.twig',
            'do'     => 'WebBundle:ChoiceQuestion:do.html.twig'
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
        if (count(array_diff($question['answer'], $answer)) == 0 && count(array_diff($answer, $question['answer'])) == 0) {
            return array('status' => 'right', 'score' => $question['score']);
        }

        if (count(array_diff($answer, $question['answer'])) == 0) {
            $percentage = intval(count($answer) / count($question['answer']) * 100);
            return array(
                'status'     => 'partRight',
                'percentage' => $percentage,
                'score'      => $question['missScore']
            );
        }

        return array('status' => 'wrong', 'score' => 0);
    }

    public function filter($fields, $mode)
    {
        if (!empty($fields['choices'])) {
            $fields['metas'] = array('choices' => $fields['choices']);
        }

        return parent::commonFilter($fields, $mode);
    }
}
