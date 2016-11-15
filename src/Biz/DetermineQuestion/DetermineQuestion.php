<?php

namespace Biz\DetermineQuestion;

use Biz\Question\Config\Question;

class DetermineQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '判断题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:DetermineQuestion:create',
            'edit'   => 'WebBundle:DetermineQuestion:edit',
            'show'   => 'WebBundle:DetermineQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:DetermineQuestion:form.html.twig',
            'edit'   => 'WebBundle:DetermineQuestion:form.html.twig',
            'do'     => 'WebBundle:DetermineQuestion:do.html.twig'
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
        return parent::commonFilter($fields, $mode);
    }

}
