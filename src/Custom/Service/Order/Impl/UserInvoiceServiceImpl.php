<?php
namespace Custom\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Order\UserInvoiceService;
use Topxia\Common\ArrayToolkit;

Class UserInvoiceServiceImpl extends BaseService  implements UserInvoiceService
{
	public function getUserInvoice($id)
	{
        return $this->getUserInvoiceDao()->getUserInvoice($id);
	}

	public function findUserInvoicesByUserId($userId)
	{
		return $this->getUserInvoiceDao()->findUserInvoicesByUserId($userId);
	}

    public function createUserInvoice($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'title'))) {
            throw $this->createServiceException('缺少必要字段，创建发票失败！');
        }

        $fields = $this->_filterUserInvoiceFields($fields);
        return $this->getUserInvoiceDao()->createUserInvoice($fields);  
    }
    
    public function updateUserInvoice($id, $fields)
    {
        return $this->getUserInvoiceDao()->updateUserInvoice($id,$fields);
    }

    private function _filterUserInvoiceFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'userId' => 0,
            'title' => '',
            'createdTime' => 0
        ));
        
        return $fields;
    }

    private function getUserInvoiceDao()
    {
        return $this->createDao('Custom:Order.UserInvoiceDao');
    }
}