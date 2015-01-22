<?php
namespace Custom\Service\Order\Dao;

interface OrderInvoiceDao
{

	public function getOrderInvoice($id);

	public function findOrderInvoicesByUserId($userId);

    public function createOrderInvoice(array $fields);
    
    public function updateOrderInvoice($id, array $fields);

}