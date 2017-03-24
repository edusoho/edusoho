<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;

class UserFilter extends Filter
{
    private $publicFields = array(
        'id', 'nickname', 'title'
    );

    function filter(&$data)
    {
        $data = ArrayToolkit::parts($data, $this->publicFields);
    }
}