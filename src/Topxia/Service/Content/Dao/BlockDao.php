<?php

namespace Topxia\Service\Content\Dao;

interface BlockDao
{
    public function getBlock($id);

    public function addBlock($block);

    public function deleteBlock($id);

    public function getBlockByCodeAndOrgId($code,$orgId);

    public function getBlockByCode($code);

    public function updateBlock($id, array $fields);

    public function getBlockByTemplateIdAndOrgId($blockTemplateId,$orgId=0);

    public function getBlockByTemplateId($blockTemplateId);
}