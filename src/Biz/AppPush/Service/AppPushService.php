<?php

namespace Biz\AppPush\Service;

interface AppPushService
{
    public function bindDevice($params);

    public function unbindDevice($userId);

    public function addTags($userId, $tags);

    public function batchAddTags($userIds, $tag);

    public function deleteTags($userId, $tags);

    public function batchDeleteTags($userIds, $tag);

    public function sendToTag($tag, $params);

    public function sendToUser($userId, $params);

    public function sendToUsers($userIds, $params);
}
