<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ServiceToolkit;

class CourseFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title'
    );

    protected $publicFields = array(
        'courseSetId', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price', 'originPrice',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services', 'ratingNum', 'rating',
        'taskNum', 'publishedTaskNum', 'studentNum', 'teachers', 'parentId', 'createdTime', 'updatedTime', 'enableFinish'
    );

    protected function publicFields(&$data)
    {
        if ($data['expiryStartDate']) {
            $data['expiryStartDate'] = date('c', $data['expiryStartDate']);
        }

        if ($data['expiryEndDate']) {
            $data['expiryEndDate'] = date('c', $data['expiryEndDate']);
        }

        $data['services'] = ServiceToolkit::getServicesByCodes($data['services']);

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
    }
}