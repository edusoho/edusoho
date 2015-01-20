<?php
namespace Custom\Service\Address\Dao;

interface ShippingAddressDao
{

	public function getShippingAddress($id);

	public function getDefaultShippingAddressByUserId($userId);

	public function findShippingAddressesByUserId($userId);

    public function addShippingAddress(array $fields);
    
    public function updateShippingAddress($id, array $fields);

}