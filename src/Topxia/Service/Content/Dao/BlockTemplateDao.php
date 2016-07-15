<?php

namespace Topxia\Service\Content\Dao;

interface BlockTemplateDao
{
    public function getBlockTemplate($id);

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit);

    public function searchBlockTemplateCount($conditions);

    public function addBlockTemplate($blockTemplate);

    public function deleteBlockTemplate($id);

    public function getBlockTemplateByCode($code);

    public function updateBlockTemplate($id, array $fields);
}
