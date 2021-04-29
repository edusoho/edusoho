<?php

namespace Biz\WeChat\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface SubscribeRecordDao extends AdvancedDaoInterface
{
    public function getLastRecord();
}
