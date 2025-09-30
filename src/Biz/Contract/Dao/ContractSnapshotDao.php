<?php

namespace Biz\Contract\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ContractSnapshotDao extends GeneralDaoInterface
{
    public function getByVersion($version);
}
