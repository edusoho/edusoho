<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ArrayToolkit;

class UserFilter extends Filter
{
    protected $publicFields = array(
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar'
    );

    protected function customFilter(&$data)
    {
        $data['smallAvatar'] = RequestUtil::asset($data['smallAvatar']);
        $data['mediumAvatar'] = RequestUtil::asset($data['mediumAvatar']);
        $data['largeAvatar'] = RequestUtil::asset($data['largeAvatar']);
    }
}