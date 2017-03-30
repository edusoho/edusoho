<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseSetMemberFilter extends Filter
{
    protected $publicFields = array(
        'id', 'courseId', 'userId', 'deadline', 'levelId', 'learnedNum', 'noteNum',
        'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime',
        'lastLearnTime', 'lastViewTime', 'courseSetId'
    );

    protected function customFilter(&$data)
    {
        $data['deadline'] = date('c', $data['deadline']);
        $data['noteLastUpdateTime'] = date('c', $data['noteLastUpdateTime']);
        $data['finishedTime'] = date('c', $data['finishedTime']);
        $data['lastLearnTime'] = date('c', $data['lastLearnTime']);
        $data['lastViewTime'] = date('c', $data['lastViewTime']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}