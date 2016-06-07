<?php

namespace Topxia\Service\Content\Dao;

interface BlockDao
{
    public function getBlock($id);

    public function searchBlockCount($condition);

    public function addBlock($block);

    public function deleteBlock($id);

    public function getBlockByCode($code);

    public function getBlocksByBlockTemplateIdAndOrgId($blockTemplateId,$orgId);

    public function findBlocks($condition, $sort, $start, $limit);

    public function updateBlock($id, array $fields);

    public function getBlockByTemplateId($blockTemplateId,$orgId=0);
}