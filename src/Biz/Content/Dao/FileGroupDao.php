<?php

namespace Biz\Content\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface FileGroupDao extends GeneralDaoInterface
{
    public function getByCode($code);

    public function findAll();
}
