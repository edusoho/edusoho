<?php

namespace Biz\Classroom;

use AppBundle\Common\Exception\AbstractException;

class ClassroomException extends AbstractException
{
    const EXCEPTION_MODULE = 18;

    const NOTFOUND_CLASSROOM = 4041801;

    const FORBIDDEN_MANAGE_CLASSROOM = 4031802;

    const FORBIDDEN_TAKE_CLASSROOM = 4031803;

    const FORBIDDEN_HANDLE_CLASSROOM = 4031804;

    const FORBIDDEN_LOOK_CLASSROOM = 4031805;

    const UNPUBLISHED_CLASSROOM = 5001806;

    const CHAIN_NOT_REGISTERED = 5001807;

    const UN_JOIN = 4031808;

    const EMPTY_TITLE = 5001809;

    const FORBIDDEN_UPDATE_EXPIRY_DATE = 4031810;

    const FORBIDDEN_DELETE_NOT_DRAFT = 4031811;

    const NOTFOUND_MEMBER = 4041812;

    const FORBIDDEN_BECOME_AUDITOR = 4031813;

    const FORBIDDEN_BECOME_STUDENT = 4031814;

    const EXPIRY_VALUE_LIMIT = 5001815;

    const FORBIDDEN_NOT_STUDENT = 4031816;

    const FORBIDDEN_WAVE = 5001817;

    const DUPLICATE_JOIN = 4031818;

    const RECOMMEND_REQUIRED_NUMERIC = 5001819;

    const MEMBER_LEVEL_LIMIT = 5001820;

    const MEMBER_NOT_IN_CLASSROOM = 4031821;

    const CLOSED_CLASSROOM = 5001822;

    const UNBUYABLE_CLASSROOM = 5001823;

    const EXPIRED_CLASSROOM = 5001824;

    const FORBIDDEN_AUDITOR_LEARN = 4031825;

    const EXPIRED_MEMBER = 5001826;

    const FORBIDDEN_CREATE_THREAD_EVENT = 5001827;

    public $messages = [
        4041801 => 'exception.classroom.not_found',
        4031802 => 'exception.classroom.forbidden_manage_classroom',
        4031803 => 'exception.classroom.forbidden_take_classroom',
        4031804 => 'exception.classroom.forbidden_handle_classroom',
        4031805 => 'exception.classroom.forbidden_look_classroom',
        5001806 => 'exception.classroom.unpublished_classroom',
        5001807 => 'exception.classroom.chain_not_registered',
        4031808 => 'exception.classroom.unjoin',
        5001809 => 'exception.classroom.empty_title',
        4031810 => 'exception.classroom.forbidden_update_expiry_date',
        4031811 => 'exception.classroom.forbidden_delete_not_draft',
        4041812 => 'exception.classroom.not_found_member',
        4031813 => 'exception.classroom.forbidden_become_auditor',
        4031814 => 'exception.classroom.forbidden_become_student',
        5001815 => 'exception.classroom.expiry_earlier_than_current',
        4031816 => 'exception.classroom.forbidden_not_student',
        5001817 => 'exception.classroom.forbidden_wave',
        4031818 => 'exception.classroom.duplicate_join',
        5001819 => 'exception.classroom.recommend_required_numeric',
        5001820 => 'exception.classroom.member_level_limit',
        4031821 => 'exception.classroom.member_not_in_classroom',
        5001822 => 'exception.classroom.closed_classroom',
        5001823 => 'exception.classroom.unbuyable_classroom',
        5001824 => 'exception.classroom.expired_classroom',
        4031825 => 'exception.classroom.forbidden_auditor_learn',
        5001826 => 'exception.classroom.expired_member',
        5001827 => 'exception.classroom.forbidden_create_thread_event',
    ];
}
