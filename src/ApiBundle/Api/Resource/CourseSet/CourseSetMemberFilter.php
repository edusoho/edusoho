<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Course\CourseMemberFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseSetMemberFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'user', 'deadline', 'levelId', 'learnedNum', 'noteNum',
        'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime',
        'lastLearnTime', 'lastViewTime', 'courseSetId'
    );

    protected function customFilter(&$data)
    {
        $courseMember = new CourseMemberFilter();
        $courseMember->filter($data);
    }
}