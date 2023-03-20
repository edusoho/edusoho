<?php

namespace Biz\BehaviorVerification\Service;

interface SmsRequestLogService
{
    public function isRobot($conditions);
}
