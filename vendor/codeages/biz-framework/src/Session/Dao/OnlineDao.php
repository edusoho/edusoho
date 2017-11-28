<?php

namespace Codeages\Biz\Framework\Session\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OnlineDao extends GeneralDaoInterface
{
    public function getBySessId($sessionId);

    public function deleteByDeadlineLessThan($deadline);
}
