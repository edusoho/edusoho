<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ServiceToolkit;

class CourseFilter extends Filter
{
    private $publicFields = array(
        'id', 'courseSetId', 'title', 'learnMode', 'expiryMode', 'expiryDays', 'expiryStartDate', 'expiryEndDate', 'summary',
        'goals', 'audiences', 'isDefault', 'maxStudentNum', 'status', 'creator', 'isFree', 'price',
        'vipLevelId', 'buyable', 'tryLookable', 'tryLookLength', 'watchLimit', 'services',
        'taskNum', 'publishedTaskNum', 'studentNum', 'teacherIds', 'parentId', 'createdTime', 'updatedTime'
    );

    function filter(&$data)
    {
        $data = ArrayToolkit::parts($data, $this->publicFields);
        $data['createdTime'] = date('c', $data['createdTime']);
        $data['updatedTime'] = date('c', $data['updatedTime']);
        $data['services'] = ServiceToolkit::getServicesByCodes($data['services']);
        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);

        $data['teachers'] = $data['teacherIds'];
        unset($data['teacherIds']);
        $userFilter->filters($data['teachers']);
    }
}