<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MultiClassGroupDao extends AdvancedDaoInterface
{
    public function findGroupsByIds($ids);

    public function findGroupsByMultiClassId($multiClassId);
}
