<?php

namespace Biz\SmsDefence\Service;

interface SmsDefenceService
{
    public function validate($fields);

    public function searchSmsRequestLog($conditions, $sort, $start, $limit);

    public function searchSmsBlackIpList($conditions, $sort, $start, $limit);

    public function unLockBlackIp($ip);

    public function countSmsRequestLog($conditions);

    public function countSmsBlackIpList($conditions);

    public function getSmsRequestLog($id);
}
