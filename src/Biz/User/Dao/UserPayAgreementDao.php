<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserPayAgreementDao extends GeneralDaoInterface
{
    public function getByUserIdAndBankAuth($userId, $bankAuth);

    public function getByUserId($userId);

    public function updateByUserIdAndBankAuth($userId, $bankAuth, $fields);

    public function findByUserId($userId);
}
