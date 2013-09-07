<?php

namespace Topxia\Service\User\Dao;

interface FriendDao
{
    public function addFriend($friend);

    public function deleteFriend($id);

    public function getFriendByFromIdAndToId($fromId, $toId);

    public function getFriendsByFromIdAndToIds($fromId, array $toIds);

    public function getFriend($id);

    public function findFriendsByFromId($fromId, $start, $limit);

    public function findFriendCountByFromId($fromId);

    public function findFriendsByToId($toId, $start, $limit);

    public function findFriendCountByToId($toId);
}