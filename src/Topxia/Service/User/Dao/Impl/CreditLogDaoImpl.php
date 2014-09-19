<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\CreditLogDao;

class CreditLogDaoImpl extends BaseDao implements CreditLogDao
{
	protected $table = 'credit_log';

	public function getCreditLog($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

	public function addCreditLog($CreditLog)
	{
        $affected = $this->getConnection()->insert($this->table, $CreditLog);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert ponit log error.');
        }
        return $this->getCreditLog($this->getConnection()->lastInsertId());
	}
}