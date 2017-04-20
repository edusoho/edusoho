<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\Converter;
use AppBundle\Common\ServiceToolkit;

class CourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title'
    );

    protected $publicFields = array(
        'courseSet', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'publishedTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime', 'enableFinish'
    );

    protected function publicFields(&$data)
    {
        Converter::timestampToDate($data['expiryStartDate']);
        Converter::timestampToDate($data['expiryEndDate']);

        $data['services'] = ServiceToolkit::getServicesByCodes($data['services']);

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);

        $courseSetFilter = new CourseSetFilter();
        $courseSetFilter->setMode(Filter::SIMPLE_MODE);
        $courseSetFilter->filter($data['courseSet']);
    }
}