<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;

class MeCourseFilter extends Filter
{
    /**
     * @TODO 2017-06-29 业务变更、字段变更:publishedTaskNum变更为compulsoryTaskNum,兼容一段时间
     */
    protected $publicFields = array(
        'id', 'title', 'courseSetTitle', 'learnedNum', 'courseSet', 'compulsoryTaskNum', 'publishedTaskNum', 'learnedCompulsoryTaskNum', 'progress',
    );

    protected function publicFields(&$data)
    {
        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);
    }
}
