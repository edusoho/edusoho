<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface GroupDao extends GeneralDaoInterface
{
    public function findByTitle($title);

    public function findByIds($ids);
}
