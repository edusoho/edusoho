<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class CourseMemberFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'courseId', 'deadline', 'courseSetId',
    );

    protected $publicFields = array(
        'user', 'levelId', 'learnedNum', 'noteNum', 'noteLastUpdateTime', 'isLearned', 'finishedTime', 'role', 'locked', 'createdTime', 'lastLearnTime', 'lastViewTime', 'access', 'learnedCompulsoryTaskNum',
    );

    protected function simpleFields(&$data)
    {
        if ($data['deadline']) {
            $data['deadline'] = date('c', $data['deadline']);
        }
    }

    protected function publicFields(&$data)
    {
        $data['noteLastUpdateTime'] = date('c', $data['noteLastUpdateTime']);
        $data['finishedTime'] = date('c', $data['finishedTime']);
        $data['lastLearnTime'] = date('c', $data['lastLearnTime']);
        $data['lastViewTime'] = date('c', $data['lastViewTime']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['user']);
    }
}
