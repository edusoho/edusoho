<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ClassroomThreadFilter extends Filter
{
    protected $publicFields = array(
            'id',
            'targetType',
            'targetId',
            'target',
            'title',
            'content',
            'ats',
            'nice',
            'sticky',
            'solved',
            'lastPostUserId',
            'lastPostTime',
            'userId',
            'type',
            'postNum',
            'hitNum',
            'memberNum',
            'status',
            'startTime',
            'endTime',
            'maxUsers',
            'actvityPicture',
            'location',
            'relationId',
            'categoryId',
            'createdTime',
            'updateTime',
            'updatedTime',
            'user',
    );

    protected function publicFields(&$data)
    {
        $data['content'] = $this->convertAbsoluteUrl($data['content']);
        $data['lastPostTime'] = date('c', $data['lastPostTime']);
        $data['updatedTime'] = date('c', isset($data['updatedTime']) ? $data['updatedTime'] : $data['updateTime']);
        unset($data['updateTime']);

        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filter($data['user']);
        }

        if (!empty($data['target'])) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::SIMPLE_MODE);
            $classroomFilter->filter($data['target']);
            $data['classroom'] = $data['target'];
            unset($data['target']);
        }
    }
}
