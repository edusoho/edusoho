<?php

namespace ApiBundle\Api\Resource\Homework;

use ApiBundle\Api\Resource\Filter;

class HomeworkFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'description', 'itemCount', 'latestHomeworkResult', 'createdTime', 'updatedTime',
    );

    protected function publicFields(&$data)
    {
        if (isset($data['latestHomeworkResult'])) {
            $homeworkResultFilter = new HomeworkResultFilter();
            $homeworkResultFilter->setMode(Filter::SIMPLE_MODE);
            $homeworkResultFilter->filter($data['latestHomeworkResult']);
        }
    }
}
