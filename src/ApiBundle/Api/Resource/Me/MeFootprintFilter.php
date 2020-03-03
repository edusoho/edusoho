<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Activity\ActivityFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Filter;

class MeFootprintFilter extends Filter
{
    protected $publicFields = array('id', 'userId', 'targetType', 'targetId', 'event', 'date', 'target', 'createdTime', 'updatedTime');

    protected function publicFields(&$footprint)
    {
        if (empty($footprint['target'])) {
            return $footprint;
        }

        $method = 'filter'.ucfirst($footprint['targetType']).'Footprint';

        $footprint = $this->$method($footprint);
    }

    protected function filterTaskFootprint($footprint)
    {
        if (empty($footprint)) {
            return array();
        }

        if (empty($footprint['target'])) {
            return $footprint;
        }

        $courseItemFilter = new CourseItemFilter();
        $courseItemFilter->setMode(Filter::SIMPLE_MODE);

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::SIMPLE_MODE);

        $activityFilter = new ActivityFilter();
        $activityFilter->setMode(Filter::SIMPLE_MODE);

        $course = empty($footprint['target']['course']) ? null : $footprint['target']['course'];
        $classroom = empty($footprint['target']['classroom']) ? null : $footprint['target']['classroom'];
        $activity = empty($footprint['target']['activity']) ? null : $footprint['target']['activity'];

        $courseFilter->filter($course);
        $activityFilter->filter($activity);

        $courseItemFilter->filter($footprint['target']);

        $footprint['target']['course'] = $course;
        $footprint['target']['classroom'] = $classroom;
        $footprint['target']['activity'] = $activity;

        return $footprint;
    }
}
