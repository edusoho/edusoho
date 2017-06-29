<?php

namespace Biz\User\Dao;

interface MessageDao
{
    public function getByFromIdAndToId($fromId, $toId);

    public function findByIds(array $ids);

    public function deleteByIds(array $ids);
}
