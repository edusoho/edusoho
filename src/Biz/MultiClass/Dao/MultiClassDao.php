<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MultiClassDao extends GeneralDaoInterface
{
    public function findByProductIds(array $productIds);

    public function getByTitle($title);
}
