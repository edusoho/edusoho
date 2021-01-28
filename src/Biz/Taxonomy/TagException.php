<?php

namespace Biz\Taxonomy;

use AppBundle\Common\Exception\AbstractException;

class TagException extends AbstractException
{
    const EXCEPTION_MODULE = 30;

    const NOTFOUND_TAG = 4043001;

    const NOTFOUND_GROUP = 4043002;

    const EMPTY_TAG_NAME = 5003003;

    const DUPLICATE_TAG_NAME = 5003004;

    const EMPTY_GROUP_NAME = 5003005;

    const DUPLICATE_GROUP_NAME = 5003006;

    public $messages = [
        4043001 => 'exception.tag.not_found',
        4043002 => 'exception.tag.not_found_group',
        5003003 => 'exception.tag.empty_name',
        5003004 => 'exception.tag.duplicate_name',
        5003005 => 'exception.tag.empty_group_name',
        5003006 => 'exception.tag.duplicate_group_name',
    ];
}
