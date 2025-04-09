<?php

namespace ESCloud\SDK\Service;

class AppPushService extends BaseService
{
    protected $host = 'test-push-service.edusoho.cn';

    protected $service = 'push';

    public function inspectTenant()
    {
        return $this->request('GET', '/v1/tenant/inspect');
    }

    public function enableTenant()
    {
        return $this->request('POST', '/v1/tenant/enable');
    }

    public function disableTenant()
    {
        return $this->request('POST', '/v1/tenant/disable');
    }

    public function bindDevice($params)
    {
        return $this->request('POST', '/v1/device/bind', $params);
    }

    public function unbindDevice($userId)
    {
        return $this->request('POST', '/v1/device/unbind', ['userId' => $userId]);
    }

    public function addTags($userId, $tags)
    {
        return $this->request('POST', '/v1/user/addTags', ['userId' => $userId, 'tags' => $tags]);
    }

    public function batchAddTags($userIds, $tag)
    {
        return $this->request('POST', '/v1/user/addTags', ['userIds' => $userIds, 'tags' => $tags]);
    }

    public function deleteTags($userId, $tags)
    {
        return $this->request('POST', '/v1/user/deleteTags', ['userId' => $userId, 'tags' => $tags]);
    }

    public function batchDeleteTags($userIds, $tag)
    {
        return $this->request('POST', '/v1/user/batchDeleteTags', ['userIds' => $userIds, 'tags' => $tags]);
    }

    public function sendToTag($tag, $params)
    {
        return $this->request('POST', '/v1/push/sendToTag', [
            'tag' => $tag,
            'title' => $params['title'],
            'message' => $params['message'],
            'category' => $params['category'],
            'extra' => $params['extra'],
        ]);
    }

    public function sendToUser($userId, $params)
    {
        return $this->request('POST', '/v1/push/sendToUser', [
            'userId' => $userId,
            'title' => $params['title'],
            'message' => $params['message'],
            'category' => $params['category'],
            'extra' => $params['extra'],
        ]);
    }

    public function sendToUsers($userIds, $params)
    {
        return $this->request('POST', '/v1/push/sendToUsers', [
            'userIds' => $userIds,
            'title' => $params['title'],
            'message' => $params['message'],
            'category' => $params['category'],
            'extra' => $params['extra'],
        ]);
    }
}
