<?php

namespace ApiBundle\Api\Resource\Tag;

use ApiBundle\Api\Resource\Filter;

class TagFilter extends Filter
{
    protected $publicFields = ['id', 'name'];
}
