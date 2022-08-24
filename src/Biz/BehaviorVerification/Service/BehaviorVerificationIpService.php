<?php

namespace Biz\BehaviorVerification\Service;

interface BehaviorVerificationIpService
{
    public function isInBlackIpList($ip);

    public function addBlackIpList($ip);
}
