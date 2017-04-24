<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadGoodsDao extends GeneralDaoInterface
{
    public function deleteByThreadIdAndType($id, $type);

    public function sumGoodsCoins($conditions);
}
