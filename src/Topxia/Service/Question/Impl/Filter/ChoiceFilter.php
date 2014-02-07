<?php
namespace Topxia\Service\Question\Impl\Filter;

class ChoiceFilter extends AbstractFilter
{
    public function filter($fields, $mode = 'create')
    {
        $fields['type'] = count($fields['answer']) == 1 ? 'single_choice' : 'choice';
        $fields['metas'] = array('choices' => $fields['choices']);
        return $this->commonFilter($fields, $mode);
    }
}