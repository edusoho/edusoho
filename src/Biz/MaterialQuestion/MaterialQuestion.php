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
