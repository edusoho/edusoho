<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface EventDao extends GeneralDaoInterface
{
    public function getEventByActionAndLocation($action, array $location);
}
