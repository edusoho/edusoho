<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface EventDao extends AdvancedDaoInterface
{
    public function getEventByActionAndLocation($action, array $location);
}
