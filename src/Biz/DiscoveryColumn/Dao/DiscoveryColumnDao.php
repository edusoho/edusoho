<?php

namespace Biz\DiscoveryColumn\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DiscoveryColumnDao extends GeneralDaoInterface
{
    public function findByTitle($title);

    public function findAllOrderBySeq();
}
