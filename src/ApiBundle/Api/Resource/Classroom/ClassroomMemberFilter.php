<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Converter;
use AppBundle\Common\ServiceToolkit;

class ClassroomMemberFilter extends Filter
{
    protected $publicFields = array(
        'id', 'classroomId', 'userId', 'orderId', 'levelId', 'noteNum', 'threadNum', 'locked', 'remark', 'role', 'deadline', 'access'
    );
}