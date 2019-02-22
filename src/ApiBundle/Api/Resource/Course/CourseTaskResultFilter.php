<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Resource\Filter;

class CourseTaskResultFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'status',
    );

    protected $publicFields = array(
        'activityId',
        'courseId',
        'courseTaskId',
        'createdTime',
        'lastLearnTime',
        'finishedTime',
        'updatedTime',
        'userId',
    );
}
