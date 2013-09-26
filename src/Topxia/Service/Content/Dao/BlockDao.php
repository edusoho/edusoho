<?php

namespace Topxia\Service\Content\Dao;

interface BlockDao
{
    public function getBlock($id);

    public function searchBlockCount();

    public function addBlock($Block);

    public function deleteBlock($id);

    public function getBlockByCode($code);

    public function findBlocks($start, $limit);

    public function updateBlock($id, array $fields);
}