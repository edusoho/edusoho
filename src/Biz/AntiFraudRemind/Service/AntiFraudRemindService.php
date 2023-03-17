<?php

namespace Biz\AntiFraudRemind\Service;

interface AntiFraudRemindService
{
    public function getByUserId($userId);

    public function create($antiFraudRemind);

    public function update($fileId, $fields);
}
