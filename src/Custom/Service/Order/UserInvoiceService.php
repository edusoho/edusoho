<?php
namespace Custom\Service\Order;

interface UserInvoiceService
{
    public function getUserInvoice($id);

    public function findUserInvoicesByUserId($userId);

    public function createUserInvoice($fields);

    public function updateUserInvoice($id, $fields);

}