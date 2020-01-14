<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserFootprintDao extends GeneralDaoInterface
{
    public function deleteBeforeDate($date);
}
