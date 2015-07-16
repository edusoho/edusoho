<?php

namespace Topxia\Service\Content\Dao;

interface BlockDao
{
    public function getBlock($id);

    public function searchBlockCount($condition);

    public function addBlock($block);

    public function deleteBlock($id);

    public function getBlockByCode($code);

    public function findBlocks($condition, $sort, $start, $limit);

    public function updateBlock($id, array $fields);
}