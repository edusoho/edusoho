<?php

namespace Codeages\Biz\Framework\Scheduler\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface JobPoolDao extends GeneralDaoInterface
{
    public function getByName($name = 'default');
}
