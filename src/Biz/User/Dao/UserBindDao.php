<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserBindDao extends GeneralDaoInterface
{
    public function getByFromId($fromId);

    public function getByTypeAndFromId($type, $fromId);

    public function getByToIdAndType($type, $toId);

    public function getByToken($token);

    public function findByToId($toId);

    public function findByToIdAndType($type, $toId);

    public function deleteByTypeAndToId($type, $toId);

    public function findByTypeAndFromIds($type, $fromIds);

    public function findByTypeAndToIds($type, $toIds);
}
