<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResultDao extends AdvancedDaoInterface
{
    public function countGroupByEventId($eventIds);
}
