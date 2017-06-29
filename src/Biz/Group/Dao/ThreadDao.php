<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
