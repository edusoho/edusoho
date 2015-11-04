<?php

namespace Topxia\Service\User\Dao;

interface UserPayAgreementDao
{

	public function getUserPayAgreement($id);

	public function getUserPayAgreementByBankAuth($bankAuth);

	public function getUserPayAgreementByUserId($userId);
	
    public function addUserPayAgreement($field);

    public function updateUserPayAgreementByBankAuth($bankAuth,$fields);

    public function findUserPayAgreementsByUserId($userId);
 
}