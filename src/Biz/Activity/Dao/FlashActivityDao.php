<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FlashActivityDao extends GeneralDaoInterface
{
    public function findByIds($Ids);
}
