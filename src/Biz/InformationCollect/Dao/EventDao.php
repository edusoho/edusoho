<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface EventDao extends GeneralDaoInterface
{
    public function getByActionAndLocation($action, array $location);
}
