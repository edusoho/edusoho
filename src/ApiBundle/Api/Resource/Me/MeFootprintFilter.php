<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Filter;

class MeFootprintFilter extends Filter
{
    protected $publicFields = array('id', 'userId', 'targetType', 'targetId', 'event', 'date', 'target');

    protected function publicFields(&$footprint)
    {
        if (empty($footprint['id'])) {
            return;
        }

        $method = 'filter'.ucfirst($footprint['targetType']).'Footprint';

        $footprint = $this->$method($footprint);
    }

    protected function filterTaskFootprint($footprint)
    {
        if (empty($footprint)) {
            return array();
        }

        $footprint['date'] = date('c', $footprint['date']);

        $courseItemFilter = new CourseItemFilter();
        $courseItemFilter->setMode(Filter::SIMPLE_MODE);

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::SIMPLE_MODE);

        $classroomFilter = new ClassroomFilter();
        $classroomFilter->setMode(Filter::SIMPLE_MODE);

        if (empty($footprint['target'])) {
            return $footprint;
        }

        $course = empty($footprint['target']['course']) ? array() : $footprint['target']['course'];
        $classroom = empty($footprint['target']['classroom']) ? array() : $footprint['target']['classroom'];

        $courseFilter->filter($course);
        $classroomFilter->filter($classroom);

        $courseItemFilter->filter($footprint['target']);

        $footprint['target']['course'] = $course;
        $footprint['target']['classroom'] = $classroom;

        return $footprint;
    }
}
