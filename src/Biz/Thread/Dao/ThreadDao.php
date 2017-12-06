<?php

namespace Biz\Thread\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadDao extends GeneralDaoInterface
{
    public function findThreadIds($conditions);
}
