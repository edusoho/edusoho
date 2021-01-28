<?php

namespace Biz\IM;

use AppBundle\Common\Exception\AbstractException;

class ConversationException extends AbstractException
{
    const EXCEPTION_MODULE = 33;

    const NOTFOUND_CONVERSATION = 4043301;

    const FIELD_NO_REQUIRED = 5003302;

    const FIELD_USERID_REQUIRED = 5003303;

    const FIELD_MEMBERIDS_REQUIRED_ARRAY = 5003304;

    const CREATE_FAILED = 5003305;

    const NOTFOUND_MEMBER = 4043306;

    const QUIT_FAILED = 5003307;

    const JOIN_FAILED = 5003308;

    const EMPTY_MEMBERS = 5003309;

    const CONVERSATION_IS_FULL = 5003310;

    public $messages = [
        4043301 => 'exception.conversation.not_found',
        5003302 => 'exception.conversation.field_no_required',
        5003303 => 'exception.conversation.field_userid_required',
        5003304 => 'exception.conversation.field_memberids_required_array',
        5003305 => 'exception.conversation.create_conversation_failed',
        4043306 => 'exception.conversation.not_found_member',
        5003307 => 'exception.conversation.quit_conversation_failed',
        5003308 => 'exception.conversation.join_conversation_failed',
        5003309 => 'exception.conversation.empty_members',
        5003310 => 'exception.conversation.is_full',
    ];
}
