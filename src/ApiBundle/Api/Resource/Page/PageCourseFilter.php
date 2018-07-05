<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Course\CourseItemFilter;
use ApiBundle\Api\Resource\Filter;

class PageCourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'courseSetTitle',
    );
    protected $publicFields = array(
        'access', 'learnMode', 'compulsoryTaskNum', 'tryLookable', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary', 'audiences', 'goals', 'isDefault', 'maxStudentNum', 'status', 'isFree', 'price', 'originPrice', 'teachers', 'services', 'courseSet', 'courseItems', 'courses',
    );

    protected function publicFields(&$data)
    {
        $courseItems = $data['courseItems'];
        foreach ($courseItems as &$courseItem) {
            $courseItemFilter = new CourseItemFilter();
            $courseItemFilter->setMode(Filter::PUBLIC_MODE);
            $courseItemFilter->filter($courseItem);
        }

        $courses = $data['courses'];
        foreach ($courses as &$course) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::SIMPLE_MODE);
            $courseFilter->filter($course);
        }

        $courseFilter = new CourseFilter();
        $courseFilter->setMode(Filter::PUBLIC_MODE);
        $courseFilter->filter($data);
        $data['courseItems'] = $courseItems;
        $data['courses'] = $courses;
    }
}
