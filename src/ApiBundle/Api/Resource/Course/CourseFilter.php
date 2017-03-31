<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ServiceToolkit;

class CourseFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseSetId', 'title', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'publishedTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime'
    );

    protected function customFilter(&$data)
    {
        $data['services'] = ServiceToolkit::getServicesByCodes($data['services']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
    }
}