<?php

namespace Biz\SingleChoiceQuestion;

use Biz\Question\Config\Question;

class SingleChoiceQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '单选题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:SingleChoiceQuestion:create',
            'edit'   => 'WebBundle:SingleChoiceQuestion:edit',
            'show'   => 'WebBundle:SingleChoiceQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:SingleChoiceQuestion:form.html.twig',
            'edit'   => 'WebBundle:SingleChoiceQuestion:form.html.twig',
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

    public function filter($fields, $mode)
    {
        if (!empty($fields['choices'])) {
            $fields['metas'] = array('choices' => $fields['choices']);
        }

        return parent::commonFilter($fields, $mode);
    }
}
