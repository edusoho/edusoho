<?php
namespace Custom\Service\Address\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Address\Dao\ShippingAddressDao;

Class ShippingAddressDaoImpl extends BaseDao implements ShippingAddressDao
{

	protected $table = 'shipping_address';

	public function getShippingAddress($id)
	{
	    $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
	    return $this->getConnection()->fetchAssoc($sql, array($id)) ? : array();
	}

    public function getDefaultShippingAddressByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ?  AND isDefault = 1 LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : array();
    }

	public function findShippingAddressesByUserId($userId)
	{
		$sql = "SELECT * FROM {$this->table}  WHERE userId = ? ORDER BY createdTime DESC";
		return $this->getConnection()->fetchAll($sql,array($userId))? : array();
	}

    public function addShippingAddress(array $fields)
    {
    	$affected = $this->getConnection()->insert($this->table, $fields);
    	if ($affected <= 0) {
    	    throw $this->createDaoException('Insert shipping_address error.');
    	}
    	return $this->getShippingAddress($this->getConnection()->lastInsertId());
    }
    
    public function updateShippingAddress($id, array $fields)
    {
    	$this->getConnection()->update($this->table, $fields, array('id' => $id));
    	return $this->getShippingAddress($id);
    }

}