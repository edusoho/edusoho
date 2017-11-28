<?php

namespace Codeages\Biz\Framework\Session\Service;

interface OnlineService
{
    public function getOnlineBySessId($sessId);

    public function countLogined($gtSessTime);

    public function countOnline($gtSessTime);

    public function gc();

    public function searchOnlines($condition, $orderBy, $start, $limit);

    public function countOnlines($condition);

    public function saveOnline($online);
}
