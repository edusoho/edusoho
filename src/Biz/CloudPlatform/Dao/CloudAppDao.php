<?php

namespace Biz\CloudPlatform\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CloudAppDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function findByCodes(array $codes);

    public function findByTypes(array $types);

    public function find($start, $limit);

    public function countApps();
}
