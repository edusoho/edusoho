<?php
namespace Custom\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Order\Dao\UserInvoiceDao;

Class UserInvoiceDaoImpl extends BaseDao implements UserInvoiceDao
{

	protected $table = 'user_invoice';

	public function getUserInvoice($id)
	{
	    $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
	    return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findUserInvoicesByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table}  WHERE userId = ? ORDER BY createdTime DESC";
		return $this->getConnection()->fetchAll($sql,array($userId))? : array();
	}

    public function createUserInvoice(array $fields)
    {
    	$affected = $this->getConnection()->insert($this->table, $fields);
    	if ($affected <= 0) {
    	    throw $this->createDaoException('Insert user_invoice error.');
    	}
    	return $this->getUserInvoice($this->getConnection()->lastInsertId());
    }
    
    public function updateUserInvoice($id, array $fields)
    {
    	$this->getConnection()->update($this->table, $fields, array('id' => $id));
    	return $this->getUserInvoice($id);
    }

}