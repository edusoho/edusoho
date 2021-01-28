<?php

namespace Codeages\PluginBundle\Biz\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AppDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function findByType($type, $start, $limit);

    public function countByType($type);
}
