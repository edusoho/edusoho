<?php
namespace Custom\Service\Address\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Address\ShippingAddressService;
use Topxia\Common\ArrayToolkit;

Class ShippingAddressServiceImpl extends BaseService  implements ShippingAddressService
{
	public function getShippingAddress($id)
	{
        return ShippingAddressSerialize::unserialize($this->getShippingAddressDao()->getShippingAddress($id));
	}

    public function getDefaultShippingAddressByUserId($userId)
    {
        return ShippingAddressSerialize::unserialize($this->getShippingAddressDao()->getDefaultShippingAddressByUserId($userId));
    }

	public function findShippingAddressesByUserId($userId)
	{
		return ShippingAddressSerialize::unserializes($this->getShippingAddressDao()->findShippingAddresssByUserId($userId));
	}

    public function addShippingAddress($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('contactName', 'region', 'address', 'postCode'))) {
            throw $this->createServiceException('缺少必要字段，创建收获地址失败！');
        }

        $fields = $this->_filterShippingAddressFields($fields);
        $exsitAddresses = $this->getShippingAddressDao()->findShippingAddressesByUserId($fields['userId']);
        if(empty($exsitAddresses)) {
            $fields['isDefault'] = 1;
        }

        return ShippingAddressSerialize::unserialize($this->getShippingAddressDao()->addShippingAddress($fields));  
    }
    
    public function updateShippingAddress($id, $fields)
    {
        return ShippingAddressSerialize::unserialize($this->getShippingAddressDao()->updateShippingAddress($id,$fields));
    }

    private function _filterShippingAddressFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'userId' => 0,
            'contactName' => '',
            'region' => '',
            'address' => '',
            'postCode' => 0,
            'mobileNo' => 0,
            'telNo' => '',
            'isDefault' => 0
        ));
        
        return $fields;
    }

    private function getShippingAddressDao()
    {
        return $this->createDao('Custom:Address.ShippingAddressDao');
    }
}

class ShippingAddressSerialize
{
    public static function serialize(array $shippingAddress)
    {
        return $shippingAddress;
    }

    public static function unserialize(array $shippingAddress = null)
    {
        if (empty($shippingAddress)) {
            return $shippingAddress;
        }

        if(!empty($shippingAddress['telNo'])) {
            $shippingAddress['telNo'] = explode('-', $shippingAddress['telNo']);
        }

        return $shippingAddress;
    }

    public static function unserializes(array $shippingAddresses)
    {
        return array_map(function($shippingAddress) {
            return ShippingAddressSerialize::unserialize($shippingAddress);
        }, $shippingAddresses);
    }
}