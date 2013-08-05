<?php

namespace Topxia\Service\User\Dao;

interface FriendDao
{
    public function addFriend($friend);

    public function deleteFriend($id);

    public function getFriendByFromIdAndToId($fromId, $toId);

    public function getFriendsByFromIdAndToIds($fromId, array $toIds);

    public function getFriend($id);

    public function getFriendByFromId($fromId);
}