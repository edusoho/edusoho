<?php

namespace ApiBundle\Api\Resource\SearchKeyword;

use ApiBundle\Api\Resource\Filter;

class SearchKeywordFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'times',
    );
}
