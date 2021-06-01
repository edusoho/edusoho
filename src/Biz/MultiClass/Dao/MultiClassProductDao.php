<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MultiClassProductDao extends AdvancedDaoInterface
{
    public function getByTitle($title);

    public function findByIds($ids);

    public function getByType($type);
}
