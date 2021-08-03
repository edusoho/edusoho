<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MultiClassGroupDao extends AdvancedDaoInterface
{
    public function findByIds($ids);
}
