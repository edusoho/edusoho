<?php
namespace Custom\Service\Order;

interface OrderInvoiceService
{
    public function getOrderInvoice($id);

    public function findOrderInvoicesByUserId($userId);

    public function createOrderInvoice($fields);

    public function updateOrderInvoice($id, $fields);

}