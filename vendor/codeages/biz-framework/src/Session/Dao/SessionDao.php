<?php

namespace Codeages\Biz\Framework\Session\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SessionDao extends GeneralDaoInterface
{
    public function getBySessId($sessId);

    public function deleteBySessId($sessId);

    public function deleteBySessDeadlineLessThan($sessDeadline);
}
