<?php

namespace Codeages\Biz\Framework\Setting\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SettingDao extends GeneralDaoInterface
{
    public function getByName($name);

    public function findAll();
}
