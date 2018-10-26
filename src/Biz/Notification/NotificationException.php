<?php

namespace Biz\Notification;

use AppBundle\Common\Exception\AbstractException;

class NotificationException extends AbstractException
{
    const EXCEPTION_MODUAL = 15;

    const BATCH_NOTIFICATION_NOT_FOUND = 4041501;

    public $messages = array(
        4041501 => 'exception.batch_notification.notfound',
    );
}
