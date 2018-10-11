<?php

namespace Biz\User;

use AppBundle\Common\Exception\AbstractException;

class MessageException extends AbstractException
{
    const EXCEPTION_MODUAL = 44;

    const NOTFOUND_SENDER_OR_RECEIVER = 4044401;

    const SEND_TO_SELF = 5004402;

    const EMPTY_MESSAGE = 5004403;

    const NOTFOUND_CONVERSATION = 4044404;

    public $messages = array(
        4044401 => 'exception.message.not_found_sender_or_receiver',
        5004402 => 'exception.message.send_to_self',
        5004403 => 'exception.message.empty_message',
        4044404 => 'exception.message.not_found_conversation',
    );
}
