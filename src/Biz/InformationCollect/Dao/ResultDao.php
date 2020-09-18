<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ResultDao extends GeneralDaoInterface
{
    public function countGroupByEventId($eventIds);
}
