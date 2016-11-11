<?php

namespace Biz\ChoiceQuestion;

use Biz\Question\Config\Question;

class ChoiceQuestion extends Question
{
    public function getMetas()
    {
        return array(
            'name' => '多选题'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:ChoiceQuestion:create',
            'edit'   => 'WebBundle:ChoiceQuestion:edit',
            'show'   => 'WebBundle:ChoiceQuestion:show'
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
