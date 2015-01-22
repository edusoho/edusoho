<?php
namespace Custom\Service\Order\Impl;

use Topxia\Service\Common\BaseService;
use Custom\Service\Order\OrderInvoiceService;
use Topxia\Common\ArrayToolkit;

Class OrderInvoiceServiceImpl extends BaseService  implements OrderInvoiceService
{
	public function getOrderInvoice($id)
	{
        return $this->getOrderInvoiceDao()->getOrderInvoice($id);
	}

	public function findOrderInvoicesByUserId($userId)
	{
		return $this->getOrderInvoiceDao()->findOrderInvoicesByUserId($userId);
	}

    public function createOrderInvoice($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId', 'title'))) {
            throw $this->createServiceException('缺少必要字段，创建发票失败！');
        }

        $fields = $this->_filterOrderInvoiceFields($fields);
        return $this->getOrderInvoiceDao()->createOrderInvoice($fields);  
    }
    
    public function updateOrderInvoice($id, $fields)
    {
        return $this->getOrderInvoiceDao()->updateOrderInvoice($id,$fields);
    }

    private function _filterOrderInvoiceFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'orderId' => 0,
            'title' => '',
            'type' => '',
            'comment' => '',
            'userId' => 0,
            'amount' => 0.00,
            'createdTime' => 0
        ));
        
        return $fields;
    }

    private function getOrderInvoiceDao()
    {
        return $this->createDao('Custom:Order.OrderInvoiceDao');
    }
}