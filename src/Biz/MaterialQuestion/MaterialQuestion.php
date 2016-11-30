<?php

namespace Biz\MaterialQuestion;

use Biz\Question\Config\Question;

class MaterialQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '材料题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:MaterialQuestion:create',
            'edit'   => 'WebBundle:MaterialQuestion:edit',
            'show'   => 'WebBundle:MaterialQuestion:show'
        );
    }

    public function registerTemplates()
    {
        return array(
            'create' => 'WebBundle:MaterialQuestion:form.html.twig',
            'edit'   => 'WebBundle:MaterialQuestion:form.html.twig',
            'do'     => 'WebBundle:MaterialQuestion:do.html.twig'
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
