<?php

namespace Biz\Classroom;

use AppBundle\Common\Exception\AbstractException;

class ClassroomException extends AbstractException
{
    const EXCEPTION_MODUAL = 18;

    const NOTFOUND_CLASSROOM = 4041801;

    const FORBIDDEN_MANAGE_CLASSROOM = 4031802;

    const FORBIDDEN_TAKE_CLASSROOM = 4031803;

    const FORBIDDEN_HANDLE_CLASSROOM = 4031804;

    const FORBIDDEN_LOOK_CLASSROOM = 4031805;

    const UNPUBLISHED_CLASSROOM = 5001806;

    const CHAIN_NOT_REGISTERED = 5001807;

    public $messages = array(
        4041801 => 'exception.classroom.not_found',
        4031802 => 'exception.classroom.forbidden_manage_classroom',
        4031803 => 'exception.classroom.forbidden_take_classroom',
        4031804 => 'exception.classroom.forbidden_handle_classroom',
        4031805 => 'exception.classroom.forbidden_look_classroom',
        5001806 => 'exception.classroom.unpublished_classroom',
        5001807 => 'exception.classroom.chain_not_registered',
    );
}