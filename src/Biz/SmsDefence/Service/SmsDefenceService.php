<?php

namespace Biz\SmsDefence\Service;

interface SmsDefenceService
{
    public function validate($fields);

    public function searchSmsRequestLog($conditions, $orders, $start, $limit);

    public function searchSmsBlackIpList($conditions, $orders, $start, $limit);

    public function unLockBlackIp($ip);

    public function countSmsRequestLog($conditions);

    public function countSmsBlackIpList($conditions);
}
