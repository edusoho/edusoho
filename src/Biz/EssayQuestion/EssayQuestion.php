<?php

namespace Biz\EssayQuestion;

use Biz\Question\Config\Question;

class EssayQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '问答题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:EssayQuestion:create',
            'edit'   => 'WebBundle:EssayQuestion:edit',
            'show'   => 'WebBundle:EssayQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:EssayQuestion:form.html.twig',
            'edit'   => 'WebBundle:EssayQuestion:form.html.twig',
            'do'     => 'WebBundle:EssayQuestion:do.html.twig'
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
        return array('status' => 'none', 'score' => 0);
    }

    public function filter($fields, $mode)
    {
        return parent::commonFilter($fields, $mode);
    }

    public function isNeedCheck()
    {
        return true;
    }

}
