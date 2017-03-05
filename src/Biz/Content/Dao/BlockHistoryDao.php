<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BlockHistoryDao extends GeneralDaoInterface
{
    public function deleteByBlockId($blockId);

    public function findByBlockId($blockId, $start, $limit);

    public function countByBlockId($blockId);

    public function getLatest();

    public function getLatestByBlockId($blockId);
}
