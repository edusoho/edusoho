<?php

namespace Biz\Notification;

use AppBundle\Common\Exception\AbstractException;

class NotificationException extends AbstractException
{
    const EXCEPTION_MODULE = 15;

    const BATCH_NOTIFICATION_NOT_FOUND = 4041501;

    const PUBLISHED_BATCH_NOTIFICATION = 5001502;

    public $messages = [
        4041501 => 'exception.batch_notification.notfound',
        5001502 => 'exception.batch_notification.published',
    ];
}
