<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = array('type', 'data', 'moduleType');

    protected function publicFields(&$data)
    {
        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        if ('course_list' == $data['type'] && 'condition' == $data['data']['sourceType']) {
            foreach ($data['data']['items'] as &$course) {
                $courseFilter->filter($course);
            }
        }
    }
}
