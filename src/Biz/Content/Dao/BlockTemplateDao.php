<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BlockTemplateDao extends GeneralDaoInterface
{
    public function getByCode($code);
}
