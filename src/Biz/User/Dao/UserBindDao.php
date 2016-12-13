<?php

namespace Biz\User\Dao;

interface UserBindDao
{
    public function getByFromId($fromId);

    public function getByTypeAndFromId($type, $fromId);

    public function getByToIdAndType($type, $toId);

    public function getByToken($token);

    public function findByToId($toId);
}
