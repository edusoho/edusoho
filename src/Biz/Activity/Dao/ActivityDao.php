<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityDao extends GeneralDaoInterface
{
    public function findByIds($ids);
}
