<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class MessageException extends AbstractException
{
    const EXCEPTION_MODULE = 44;

    const NOTFOUND_SENDER_OR_RECEIVER = 4044401;

    const SEND_TO_SELF = 5004402;

    const EMPTY_MESSAGE = 5004403;

    const NOTFOUND_CONVERSATION = 4044404;

    const DELETE_DENIED = 4034405;

    const MESSAGE_SEND_LIMIT = 5004406;

    public $messages = [
        4044401 => 'exception.message.not_found_sender_or_receiver',
        5004402 => 'exception.message.send_to_self',
        5004403 => 'exception.message.empty_message',
        4044404 => 'exception.message.not_found_conversation',
        4034405 => 'exception.message.delete_denied',
        5004406 => 'exception.message.send_limit',
    ];
}
