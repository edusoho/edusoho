<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Filter;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = array('type', 'data', 'moduleType');

    protected function publicFields(&$data)
    {
        if ('course_list' == $data['type'] && 'condition' == $data['data']['sourceType']) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$course) {
                $courseFilter->filter($course);
            }
        }

        if ('classroom_list' == $data['type'] && 'condition' == $data['data']['sourceType']) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$classroom) {
                $classroomFilter->filter($classroom);
            }
        }
    }
}
