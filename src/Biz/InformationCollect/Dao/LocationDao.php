<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface LocationDao extends AdvancedDaoInterface
{
    public function findByEventIds($eventIds);
}
