<?php

namespace Biz\Notification;

use AppBundle\Common\Exception\AbstractException;

class NotificationException extends AbstractException
{
    const EXCEPTION_MODUAL = 15;

    const NOTIFICATION_NOT_FOUND = 4041501;

    public $messages = array(
        4041501 => 'exception.notification.notfound',
    );
}
