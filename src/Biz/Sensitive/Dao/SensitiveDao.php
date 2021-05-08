<?php

namespace Biz\Sensitive\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface SensitiveDao extends AdvancedDaoInterface
{
    public function getByName($name);

    public function findAllKeywords();

    public function findByState($state);
}
