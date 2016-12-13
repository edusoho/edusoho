<?php

namespace Biz\User\Dao;

interface UserPayAgreementDao
{
    public function getByUserIdAndBankAuth($userId, $bankAuth);

    public function getByUserId($userId);

    public function updateByUserIdAndBankAuth($userId, $bankAuth, $fields);

    public function findByUserId($userId);
}
