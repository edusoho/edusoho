<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PptActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
