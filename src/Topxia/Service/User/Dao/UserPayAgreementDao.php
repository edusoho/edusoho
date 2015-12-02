<?php

namespace Topxia\Service\User\Dao;

interface UserPayAgreementDao
{
    public function getUserPayAgreement($id);

    public function getUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth);

    public function getUserPayAgreementByUserId($userId);

    public function addUserPayAgreement($field);

    public function updateUserPayAgreementByUserIdAndBankAuth($userId, $bankAuth, $fields);

    public function findUserPayAgreementsByUserId($userId);

    public function deleteUserPayAgreements($id);

}
