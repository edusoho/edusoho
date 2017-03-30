<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use AppBundle\Common\ArrayToolkit;

class CourseSetMemberFilter extends Filter
{
    private $publicFields = array(
        'id', 'courseId', 'userId', 'deadline', 'levelId', 'learnedNum', 'noteNum',
        'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime',
        'lastLearnTime', 'lastViewTime', 'courseSetId'
    );

    function filter(&$data)
    {
        $data = ArrayToolkit::parts($data, $this->publicFields);
        $data['createdTime'] = date('c', $data['createdTime']);
        $data['deadline'] = date('c', $data['deadline']);
        $data['noteLastUpdateTime'] = date('c', $data['noteLastUpdateTime']);
        $data['finishedTime'] = date('c', $data['finishedTime']);
        $data['lastLearnTime'] = date('c', $data['lastLearnTime']);
        $data['lastViewTime'] = date('c', $data['lastViewTime']);


        $data['user'] = $data['userId'];
        unset($data['userId']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}