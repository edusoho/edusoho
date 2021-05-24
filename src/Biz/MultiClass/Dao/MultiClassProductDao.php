<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MultiClassProductDao extends GeneralDaoInterface
{
    public function getByTitle($title);

    public function findByIds($ids);

    public function getByType($type);
}
