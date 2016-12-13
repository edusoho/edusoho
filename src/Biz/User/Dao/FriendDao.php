<?php

namespace Biz\User\Dao;

interface FriendDao
{
    public function updateByFromIdAndToId($fromId, $toId, $fields);

    public function getByFromIdAndToId($fromId, $toId);

    public function findByFromIdAndToIds($fromId, array $toIds);

    public function findByFromId($fromId, $start, $limit);

    public function findAllUserFollowingByFromId($fromId);

    public function findAllUserFollowerByToId($toId);

    public function countByFromId($fromId);

    public function findByToId($toId, $start, $limit);

    public function countByToId($toId);

    public function findByUserId($userId, $start, $limit);

    public function countByUserId($userId);
}
