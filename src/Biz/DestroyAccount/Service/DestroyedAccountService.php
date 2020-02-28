<?php

namespace Biz\DestroyAccount\Service;

interface DestroyedAccountService
{
    public function createDestroyedAccount($fields);

    public function getDestroyedAccount($id);

    public function searchDestroyedAccounts($conditions, $sort, $start, $limit, $columns = array());

    public function countDestroyedAccounts($conditions);
}
