<?php
namespace Topxia\Service\Question\Impl\Filter;

class DefaultFilter extends AbstractFilter
{
    public function filter($fields, $mode = 'create')
    {
        return $this->commonFilter($fields, $mode);
    }
}