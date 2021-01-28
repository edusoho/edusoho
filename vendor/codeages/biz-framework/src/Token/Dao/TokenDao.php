<?php

namespace Codeages\Biz\Framework\Token\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TokenDao extends GeneralDaoInterface
{
    public function getByKey($key);

    public function deleteExpired($timestamp);
}
