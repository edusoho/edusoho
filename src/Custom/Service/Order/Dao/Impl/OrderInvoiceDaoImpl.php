<?php
namespace Custom\Service\Order\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Order\Dao\OrderInvoiceDao;

Class OrderInvoiceDaoImpl extends BaseDao implements OrderInvoiceDao
{

	protected $table = 'order_invoice';

	public function getOrderInvoice($id)
	{
	    $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
	    return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findOrderInvoicesByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table}  WHERE userId = ? ORDER BY createdTime DESC";
		return $this->getConnection()->fetchAll($sql,array($userId))? : array();
	}

    public function createOrderInvoice(array $fields)
    {
    	$affected = $this->getConnection()->insert($this->table, $fields);
    	if ($affected <= 0) {
    	    throw $this->createDaoException('Insert user_invoice error.');
    	}
    	return $this->getOrderInvoice($this->getConnection()->lastInsertId());
    }
    
    public function updateOrderInvoice($id, array $fields)
    {
    	$this->getConnection()->update($this->table, $fields, array('id' => $id));
    	return $this->getOrderInvoice($id);
    }

}