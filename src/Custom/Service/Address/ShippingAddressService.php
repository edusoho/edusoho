<?php
namespace Custom\Service\Address;

interface ShippingAddressService
{
    public function getShippingAddress($id);

    public function getDefaultShippingAddressByUserId($userId);
    
    public function findShippingAddressesByUserId($userId);

    public function addShippingAddress($fields);

    public function updateShippingAddress($id, $fields);

}