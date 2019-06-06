<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Exception\SDKException;

class NotificationService extends BaseService
{
    const SNS_MAX_COUNT = 50;

    protected $host = 'notifition-service.qiqiuyun.net';

    public function openAccount($channel, $params)
    {
        $params['type'] = $channel;
        return $this->request('POST', '/accounts', $params);
    }

    public function closeAccount($channel)
    {
        return $this->request('DELETE', "/accounts/{$channel}");
    }

    public function sendNotifications($notifications)
    {
        return $this->request('POST', '/notifications', $notifications);
    }

    public function getNotification($sn)
    {
        return $this->request('GET', "/notifications/{$sn}");
    }

    public function batchGetNotifications($sns)
    {
        if (self::SNS_MAX_COUNT == count($sns)) {
            throw new SDKException('sn count out of limit');
        }
        return $this->request('GET', "/notifications", array('sns' => $sns));
    }
}