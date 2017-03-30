<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ArrayToolkit;

class UserFilter extends Filter
{
    private $publicFields = array(
        'id', 'nickname', 'title', 'smallAvatar', 'mediumAvatar', 'largeAvatar'
    );

    function filter(&$data)
    {
        $data = ArrayToolkit::parts($data, $this->publicFields);
        $data['smallAvatar'] = RequestUtil::asset($data['smallAvatar']);
        $data['mediumAvatar'] = RequestUtil::asset($data['mediumAvatar']);
        $data['largeAvatar'] = RequestUtil::asset($data['largeAvatar']);
    }
}