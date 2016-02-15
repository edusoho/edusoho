<?php

namespace Topxia\Service\User\Dao;

interface FriendDao
{
    public function addFriend($friend);

    public function deleteFriend($id);

    public function updateFriendByFromIdAndToId($fromId, $toId, $fields);

    public function getFriendByFromIdAndToId($fromId, $toId);

    public function getFriendsByFromIdAndToIds($fromId, array $toIds);

    public function getFriend($id);

    public function findFriendsByFromId($fromId, $start, $limit);
    
    public function findAllUserFollowingByFromId($fromId);

    public function findAllUserFollowerByToId($toId);

    public function findFriendCountByFromId($fromId);

    public function findFriendsByToId($toId, $start, $limit);

    public function findFriendCountByToId($toId);

    public function findFriendsByUserId($userId, $start, $limit);

    public function findFriendCountByUserId($userId);
}