<?php

namespace Biz\QuestionTag\Exception;

use AppBundle\Common\Exception\AbstractException;

class QuestionTagException extends AbstractException
{
    const EXCEPTION_MODULE = 92;

    const TAG_GROUP_NAME_DUPLICATE = 4009201;

    const TAG_GROUP_NOT_FOUND = 4049202;

    const TAG_NAME_DUPLICATE = 4009203;

    const TAG_NOT_FOUND = 4049204;

    public $messages = [
        4009201 => 'exception.question_tag.tag_group_name_duplicate',
        4046602 => 'exception.question_tag.tag_group_not_found',
        4036603 => 'exception.question_tag.tag_name_duplicate',
        4049204 => 'exception.question_tag.tag_not_found',
    ];
}
