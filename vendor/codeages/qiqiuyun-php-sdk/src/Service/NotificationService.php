<?php

namespace QiQiuYun\SDK\Service;

use QiQiuYun\SDK\Exception\SDKException;

class NotificationService extends BaseService
{
    const SNS_MAX_COUNT = 50;

    protected $host = 'notification-service.cn';

    public function openAccount()
    {
        return $this->request('POST', '/accounts');
    }

    public function closeAccount()
    {
        return $this->request('DELETE', "/accounts");
    }

    public function openChannel($channelType, $params)
    {
        $params['type'] = $channelType;
        return $this->request('POST', "/channels", $params);
    }

    public function closeChannel($channelType)
    {
        return $this->request('DELETE', "/channels/{$channelType}");
    }

    public function sendNotifications($notifications)
    {
        return $this->request('POST', '/notifications', $notifications);
    }

    public function getNotification($sn)
    {
        return $this->request('GET', "/notifications/{$sn}");
    }

    public function searchNotifications($conditions, $offset = 0, $limit = 30)
    {
        $params = array_merge($conditions, array('offset' => $offset, 'limit' => $limit));
        return $this->request('GET', "/notifications", $params); 
    }

    public function batchGetNotifications($sns)
    {
        if (self::SNS_MAX_COUNT == count($sns)) {
            throw new SDKException('sn count out of limit');
        }

        $params = array(
            'sns' => $sns,
            'offset' => 0,
            'limit' => count($sns),
        );
        return $this->request('GET', "/notifications", $params);
    }
}