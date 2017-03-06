<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BlockDao extends GeneralDaoInterface
{
    public function getByCodeAndOrgId($code, $orgId);

    public function getByCode($code);

    public function getByTemplateIdAndOrgId($blockTemplateId, $orgId = 0);

    public function getByTemplateId($blockTemplateId);
}
