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
