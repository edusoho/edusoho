<?php

namespace Topxia\Service\User\Dao;

interface UserPayAgreementDao
{

	public function getUserPayAgreement($id);
	
    public function addUserPayAgreement($field);

    public function updateUserPayAgreement($bankAuth,$fields);

    public function getUserPayAgreementByBankAuth($bankAuth);

    public function findUserPayAgreementsByUserId($userId);

    public function getUserPayAgreementByUserId($userId);

}