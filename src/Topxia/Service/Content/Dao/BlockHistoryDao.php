<?php

namespace Topxia\Service\Content\Dao;

interface BlockHistoryDao
{
    public function getBlockHistory($id);

    public function addBlockHistory($BlockHistory);

    public function deleteBlockHistory($id);

    public function deleteBlockHistoryByBlockId($blockId);

    public function findBlockHistorysByBlockId($blockId, $start, $limit);

    public function findBlockHistoryCountByBlockId($blockId);

    public function getLatestBlockHistory();
}