<?php

namespace AppBundle\Common;

class MessageToolkit
{
    public static function convertMessageToKey($message)
    {
        $messageToKey = array(
            'User is disabled.' => 'exception.educloud.user_disabled_hint',
        );

        if (isset($messageToKey[$message])) {
            return $messageToKey[$message];
        }

        return 'exception.common_error';
    }
}
