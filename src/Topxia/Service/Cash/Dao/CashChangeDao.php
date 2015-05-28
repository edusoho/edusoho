<?php

namespace Topxia\Service\Cash\Dao;

interface CashChangeDao
{

    public function getChange($id);

    public function getChangeByUserId($userId, $lock = false);

    public function addChange($fields);

    public function waveCashField($id, $value);

}