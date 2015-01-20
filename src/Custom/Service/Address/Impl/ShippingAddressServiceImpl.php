<?php
namespace Custom\Service\Address\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Address\ShippingAddressService;
use Topxia\Common\ArrayToolkit;

Class ShippingAddressServiceImpl extends BaseService  implements ShippingAddressService
{
	public function getShippingAddress($id)
	{
        return $this->getShippingAddressDao()->getShippingAddress($id);
	}

    public function getDefaultShippingAddressByUserId($userId)
    {
        return $this->getShippingAddressDao()->getDefaultShippingAddressByUserId($userId);
    }

	public function findShippingAddressesByUserId($userId)
	{
		return $this->getShippingAddressDao()->findShippingAddresssByUserId($userId);
	}

    public function addShippingAddress($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('contactName', 'region', 'address', 'postcode'))) {
            throw $this->createServiceException('缺少必要字段，创建收获地址失败！');
        }

        $fields = $this->_filterShippingAddressFields($fields);
        $exsitAddresses = $this->getShippingAddressDao()->findShippingAddressesByUserId($fields['userId']);
        if(empty($exsitAddresses)) {
            $fields['isDefault'] = 1;
        }

        return $this->getShippingAddressDao()->createShippingAddress($fields);  
    }
    
    public function updateShippingAddress($id, $fields)
    {
        return $this->getShippingAddressDao()->updateShippingAddress($id,$fields);
    }

    private function _filterShippingAddressFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'userId' => 0,
            'contactName' => '',
            'region' => '',
            'address' => '',
            'postcode' => 0,
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