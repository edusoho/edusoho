<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FileDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function find($start, $limit);

    public function countAll();

    public function findByGroupId($groupId, $start, $limit);

    public function countByGroupId($groupId);

    public function deleteByUri($uri);
}
