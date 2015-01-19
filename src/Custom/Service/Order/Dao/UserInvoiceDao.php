<?php
namespace Custom\Service\Order\Dao;

interface UserInvoiceDao
{

	public function getUserInvoice($id);

	public function findUserInvoicesByUserId($userId);

    public function createUserInvoice(array $fields);
    
    public function updateUserInvoice($id, array $fields);

}