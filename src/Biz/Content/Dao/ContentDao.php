<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ContentDao extends GeneralDaoInterface
{
    public function getByAlias($alias);
}
