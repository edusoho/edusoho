<?php

namespace AppBundle\Common;

class MessageToolkit
{
    public static function convertMessageToKey($message)
    {
        if (preg_match('/user is disabled/i', $message)) {
            return 'exception.educloud.user_disabled_hint';
        }

        return 'exception.common_error';
    }
}
