<?php

namespace Topxia\Service\Cash\Dao;

interface CashAccountDao
{

    public function getAccount($id);

    public function getAccountByUserId($userId, $lock = false);

    public function findAccountsByUserIds($userIds);

    public function addAccount($fields);

    public function updateAccount($id, $fields);

    public function waveCashField($id, $value);

    public function waveDownCashField($id, $value);

    public function searchAccount($conditions, $orderBy, $start, $limit);

    public function searchAccountCount($conditions);

}