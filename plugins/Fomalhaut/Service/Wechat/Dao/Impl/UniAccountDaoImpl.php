<?php
namespace Fomalhaut\Service\Wechat\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Fomalhaut\Service\Wechat\Dao\UniAccountDao;
use PDO;

class UniAccountDaoImpl extends BaseDao implements UniAccountDao
{
    protected $table = 'wechat_uni_account';

    public function addUniAccount($uniAcct)
    {
        $affected = $this->getConnection()->insert($this->table, $uniAcct);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert uniAcct error.');
        }
        return true;
    }
}
