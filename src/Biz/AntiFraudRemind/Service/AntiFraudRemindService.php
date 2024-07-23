<?php

namespace Biz\AntiFraudRemind\Service;

interface AntiFraudRemindService
{
    public function getByUserId($userId);

    public function creatAntiFraudRemind($antiFraudRemind);

    public function updateLastRemindTime($fileId, $fields);
}
